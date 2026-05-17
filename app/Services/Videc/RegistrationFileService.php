<?php

namespace App\Services\Videc;

use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Models\PortalUser;
use App\Models\Registration;
use App\Models\RegistrationFile;
use App\Models\RegistrationFileLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationFileService
{
    public function uploadTemporary(
        Event $event,
        string $email,
        string $fieldKey,
        UploadedFile $file,
        string $source,
        ?PortalUser $portalUser = null,
    ): RegistrationFile {
        $normalizedEmail = Str::lower(trim($email));
        $template = $this->resolveFileTemplate($event, $fieldKey);

        $this->validateUploadedFile($file, $template);

        $fileId = $this->generateFileId();
        $extension = $this->resolveFileExtension($file);
        $disk = (string) config('registration_files.disk', 'local');
        $path = $this->buildStoragePath($event, $fileId, $extension);

        Storage::disk($disk)->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        $registrationFile = RegistrationFile::query()->create([
            'file_id' => $fileId,
            'event_id' => $event->id,
            'portal_user_id' => $portalUser?->id,
            'registration_id' => null,
            'client_id' => null,
            'field_key' => $fieldKey,
            'owner_email' => $normalizedEmail,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $this->sanitizeOriginalName($file->getClientOriginalName()),
            'extension' => $extension,
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'status' => RegistrationFile::STATUS_TEMP,
            'uploaded_by_type' => $source,
            'uploaded_by_id' => $portalUser?->id,
            'uploaded_at' => now(),
            'expires_at' => now()->addHours(max(1, (int) config('registration_files.temporary_ttl_hours', 24))),
            'metadata' => [
                'event_code' => $event->code,
                'field_template_required' => (bool) $template->required,
            ],
        ]);

        $this->writeFileLog(
            $registrationFile,
            'uploaded',
            $portalUser ? 'portal_user' : 'public_registration',
            $portalUser ? (string) $portalUser->id : $normalizedEmail,
            $portalUser,
            [
                'source' => $source,
                'event_code' => $event->code,
                'field_key' => $fieldKey,
            ]
        );

        return $registrationFile;
    }

    public function attachSubmittedFiles(
        Event $event,
        PortalUser $portalUser,
        Registration $registration,
        array $customFields,
        ?Client $client = null,
        bool $enforceRequired = false,
    ): array {
        $fileTemplates = $this->getFileTemplates($event)->keyBy('name');

        if ($fileTemplates->isEmpty()) {
            return $customFields;
        }

        if ($enforceRequired) {
            $this->assertRequiredFileFieldsPresent($fileTemplates, $registration, $customFields);
        }

        foreach ($customFields as $fieldKey => $value) {
            if (!$fileTemplates->has($fieldKey)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (!is_string($value)) {
                throw ValidationException::withMessages([
                    "custom_fields.{$fieldKey}" => 'File field must contain a file_id string.',
                ]);
            }

            $attachedFile = $this->attachFileIdToRegistration(
                $event,
                $portalUser,
                $registration,
                $fieldKey,
                trim($value),
                $client,
            );

            $customFields[$fieldKey] = $attachedFile->file_id;
        }

        return $customFields;
    }

    public function mapActiveFilesByField(Registration $registration): array
    {
        return RegistrationFile::query()
            ->where('registration_id', $registration->id)
            ->where('status', RegistrationFile::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (RegistrationFile $file) {
                return [
                    $file->field_key => [
                        'file_id' => $file->file_id,
                        'name' => $file->original_name,
                        'mime_type' => $file->mime_type,
                        'size_bytes' => $file->size_bytes,
                        'uploaded_at' => optional($file->uploaded_at)?->toISOString(),
                        'attached_at' => optional($file->attached_at)?->toISOString(),
                    ],
                ];
            })
            ->all();
    }

    public function authorizePortalDownload(PortalUser $portalUser, int $eventId, string $fileId): RegistrationFile
    {
        $registrationFile = RegistrationFile::query()
            ->where('file_id', $fileId)
            ->where('event_id', $eventId)
            ->firstOrFail();

        if ((int) $registrationFile->portal_user_id !== (int) $portalUser->id) {
            throw ValidationException::withMessages([
                'file_id' => 'You do not have permission to access this file.',
            ]);
        }

        if ($registrationFile->status === RegistrationFile::STATUS_TEMP) {
            throw ValidationException::withMessages([
                'file_id' => 'Temporary file is not downloadable.',
            ]);
        }

        if ($registrationFile->registration && (int) $registrationFile->registration->portal_user_id !== (int) $portalUser->id) {
            throw ValidationException::withMessages([
                'file_id' => 'You do not have permission to access this file.',
            ]);
        }

        return $registrationFile;
    }

    public function streamDownloadResponse(RegistrationFile $registrationFile): StreamedResponse
    {
        $disk = Storage::disk($registrationFile->disk);

        if (!$disk->exists($registrationFile->path)) {
            abort(404, 'File not found.');
        }

        $filename = $registrationFile->original_name ?: ($registrationFile->file_id . '.' . $registrationFile->extension);
        $mimeType = $registrationFile->mime_type ?: 'application/octet-stream';

        return response()->streamDownload(function () use ($disk, $registrationFile) {
            $stream = $disk->readStream($registrationFile->path);

            if ($stream === false) {
                return;
            }

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function markDownloaded(RegistrationFile $registrationFile, PortalUser $portalUser): void
    {
        $this->writeFileLog(
            $registrationFile,
            'downloaded',
            'portal_user',
            (string) $portalUser->id,
            $portalUser,
            [
                'registration_id' => $registrationFile->registration_id,
                'field_key' => $registrationFile->field_key,
            ]
        );
    }

    public function cleanupExpiredTemporaryFiles(int $hours = 24): array
    {
        $threshold = now()->subHours(max(1, $hours));

        $files = RegistrationFile::query()
            ->where('status', RegistrationFile::STATUS_TEMP)
            ->whereNull('attached_at')
            ->whereNull('registration_id')
            ->where(function ($query) use ($threshold) {
                $query->where('created_at', '<=', $threshold)
                    ->orWhere(function ($expiresQuery) {
                        $expiresQuery->whereNotNull('expires_at')
                            ->where('expires_at', '<=', now());
                    });
            })
            ->get();

        $deletedStorageCount = 0;

        foreach ($files as $file) {
            $disk = Storage::disk($file->disk);

            if ($disk->exists($file->path)) {
                $disk->delete($file->path);
                $deletedStorageCount++;
            }

            $file->forceFill([
                'status' => RegistrationFile::STATUS_EXPIRED,
                'metadata' => array_merge($file->metadata ?? [], [
                    'cleaned_up_at' => now()->toISOString(),
                ]),
            ])->save();

            $this->writeFileLog(
                $file,
                'cleaned_up',
                'system',
                'videc:cleanup-registration-files',
                null,
                [
                    'path' => $file->path,
                    'disk' => $file->disk,
                ]
            );
        }

        return [
            'matched' => $files->count(),
            'deleted_from_storage' => $deletedStorageCount,
        ];
    }

    private function attachFileIdToRegistration(
        Event $event,
        PortalUser $portalUser,
        Registration $registration,
        string $fieldKey,
        string $fileId,
        ?Client $client = null,
    ): RegistrationFile {
        if (!preg_match('/^rf_[A-Z0-9]{26}$/', $fileId)) {
            throw ValidationException::withMessages([
                "custom_fields.{$fieldKey}" => 'Invalid file_id format.',
            ]);
        }

        $this->resolveFileTemplate($event, $fieldKey);

        return DB::transaction(function () use ($event, $portalUser, $registration, $fieldKey, $fileId, $client) {
            $newFile = RegistrationFile::query()
                ->where('file_id', $fileId)
                ->lockForUpdate()
                ->first();

            if (!$newFile) {
                throw ValidationException::withMessages([
                    "custom_fields.{$fieldKey}" => 'Uploaded file was not found.',
                ]);
            }

            if ((int) $newFile->event_id !== (int) $event->id) {
                throw ValidationException::withMessages([
                    "custom_fields.{$fieldKey}" => 'Uploaded file does not belong to this event.',
                ]);
            }

            if ($newFile->field_key !== $fieldKey) {
                throw ValidationException::withMessages([
                    "custom_fields.{$fieldKey}" => 'Uploaded file does not match this field.',
                ]);
            }

            $this->assertFileClaimableByPortalUser($newFile, $portalUser);

            if ($newFile->registration_id && (int) $newFile->registration_id !== (int) $registration->id) {
                throw ValidationException::withMessages([
                    "custom_fields.{$fieldKey}" => 'Uploaded file is already attached to another registration.',
                ]);
            }

            $existingActiveFile = RegistrationFile::query()
                ->where('registration_id', $registration->id)
                ->where('field_key', $fieldKey)
                ->where('status', RegistrationFile::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($existingActiveFile && (int) $existingActiveFile->id !== (int) $newFile->id) {
                $existingActiveFile->forceFill([
                    'status' => RegistrationFile::STATUS_REPLACED,
                    'replaced_by_id' => $newFile->id,
                    'replaced_at' => now(),
                ])->save();

                $this->writeFileLog(
                    $existingActiveFile,
                    'replaced',
                    'portal_user',
                    (string) $portalUser->id,
                    $portalUser,
                    [
                        'replaced_by_file_id' => $newFile->file_id,
                    ]
                );
            }

            $newFile->forceFill([
                'portal_user_id' => $portalUser->id,
                'registration_id' => $registration->id,
                'client_id' => $client?->id,
                'owner_email' => Str::lower($portalUser->email),
                'status' => RegistrationFile::STATUS_ACTIVE,
                'attached_at' => now(),
                'expires_at' => null,
                'metadata' => array_merge($newFile->metadata ?? [], [
                    'attached_from' => $newFile->uploaded_by_type,
                    'attached_event_code' => $event->code,
                ]),
            ])->save();

            $this->writeFileLog(
                $newFile,
                'attached',
                'portal_user',
                (string) $portalUser->id,
                $portalUser,
                [
                    'registration_id' => $registration->id,
                    'client_id' => $client?->id,
                ]
            );

            return $newFile;
        });
    }

    private function assertRequiredFileFieldsPresent(Collection $fileTemplates, Registration $registration, array $customFields): void
    {
        $errors = [];

        foreach ($fileTemplates as $template) {
            if (!(bool) $template->required) {
                continue;
            }

            $fieldKey = (string) $template->name;
            $incomingValue = $customFields[$fieldKey] ?? null;
            $hasIncoming = is_string($incomingValue) && trim($incomingValue) !== '';

            $hasExisting = RegistrationFile::query()
                ->where('registration_id', $registration->id)
                ->where('field_key', $fieldKey)
                ->where('status', RegistrationFile::STATUS_ACTIVE)
                ->exists();

            if (!$hasIncoming && !$hasExisting) {
                $errors["custom_fields.{$fieldKey}"] = 'This file field is required.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertFileClaimableByPortalUser(RegistrationFile $file, PortalUser $portalUser): void
    {
        $ownerEmail = Str::lower((string) $file->owner_email);
        $portalEmail = Str::lower((string) $portalUser->email);

        if ($ownerEmail !== '' && $ownerEmail !== $portalEmail) {
            throw ValidationException::withMessages([
                'file_id' => 'Uploaded file owner mismatch.',
            ]);
        }

        if ($file->portal_user_id !== null && (int) $file->portal_user_id !== (int) $portalUser->id) {
            throw ValidationException::withMessages([
                'file_id' => 'Uploaded file belongs to another portal user.',
            ]);
        }
    }

    private function validateUploadedFile(UploadedFile $file, CustomFieldTemplate $template): void
    {
        $maxSizeKb = max(1, (int) config('registration_files.max_size_kb', 2048));
        $allowedExtensions = collect(config('registration_files.allowed_extensions', []))
            ->map(fn ($ext) => strtolower((string) $ext))
            ->filter()
            ->values();
        $allowedMimeTypes = collect(config('registration_files.allowed_mime_types', []))
            ->map(fn ($mime) => strtolower((string) $mime))
            ->filter()
            ->values();

        $extension = $this->resolveFileExtension($file);
        $mimeType = strtolower((string) $file->getMimeType());
        $sizeKb = ((int) $file->getSize()) / 1024;

        if (!$allowedExtensions->contains($extension)) {
            throw ValidationException::withMessages([
                'file' => 'Only PDF, JPG, JPEG, PNG are allowed.',
            ]);
        }

        if (!$allowedMimeTypes->contains($mimeType)) {
            throw ValidationException::withMessages([
                'file' => 'Invalid file MIME type.',
            ]);
        }

        if ($sizeKb > $maxSizeKb) {
            throw ValidationException::withMessages([
                'file' => 'The file must not be greater than 2MB.',
            ]);
        }

        if (!empty($template->accepts)) {
            $accepted = collect(json_decode((string) $template->accepts, true) ?: [])
                ->map(fn ($ext) => strtolower(ltrim((string) $ext, '.')))
                ->filter()
                ->values();

            if ($accepted->isNotEmpty() && !$accepted->contains($extension)) {
                throw ValidationException::withMessages([
                    'file' => 'File extension is not allowed for this field.',
                ]);
            }
        }
    }

    private function resolveFileTemplate(Event $event, string $fieldKey): CustomFieldTemplate
    {
        $template = CustomFieldTemplate::query()
            ->where('event_id', $event->id)
            ->where('name', $fieldKey)
            ->where('type', CustomFieldTemplate::TYPE_FILE)
            ->first();

        if (!$template) {
            throw ValidationException::withMessages([
                'field_key' => 'The provided field is not a file field for this event.',
            ]);
        }

        return $template;
    }

    private function getFileTemplates(Event $event): Collection
    {
        return CustomFieldTemplate::query()
            ->where('event_id', $event->id)
            ->where('type', CustomFieldTemplate::TYPE_FILE)
            ->get();
    }

    private function resolveFileExtension(UploadedFile $file): string
    {
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: ''));

        if ($extension === 'jpeg') {
            return 'jpeg';
        }

        if ($extension === 'jpg') {
            return 'jpg';
        }

        return $extension;
    }

    private function buildStoragePath(Event $event, string $fileId, string $extension): string
    {
        $prefix = trim((string) config('registration_files.root_prefix', 'private/registration-files'), '/');

        return sprintf(
            '%s/%d/%s/%s.%s',
            $prefix,
            $event->id,
            now()->format('Y/m/d'),
            $fileId,
            $extension
        );
    }

    private function sanitizeOriginalName(string $name): string
    {
        $basename = trim($name);
        if ($basename === '') {
            return 'document';
        }

        return Str::limit(str_replace(["\r", "\n"], '', $basename), 255, '');
    }

    private function generateFileId(): string
    {
        return 'rf_' . Str::upper((string) Str::ulid());
    }

    private function writeFileLog(
        RegistrationFile $registrationFile,
        string $action,
        ?string $actorType,
        ?string $actorRef,
        ?PortalUser $portalUser,
        array $metadata = [],
        ?string $message = null,
    ): void {
        RegistrationFileLog::query()->create([
            'registration_file_id' => $registrationFile->id,
            'event_id' => $registrationFile->event_id,
            'portal_user_id' => $portalUser?->id,
            'action' => $action,
            'actor_type' => $actorType,
            'actor_ref' => $actorRef,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }
}
