<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Videc\PortalFileDownloadRequest;
use App\Http\Requests\Api\Videc\PortalRegistrationFileUploadRequest;
use App\Http\Requests\Api\Videc\RegistrationFileUploadRequest;
use App\Models\Event;
use App\Services\Videc\PortalSessionService;
use App\Services\Videc\RegistrationFileService;

class RegistrationFileController extends Controller
{
    public function __construct(
        private readonly RegistrationFileService $registrationFileService,
        private readonly PortalSessionService $portalSessionService,
    ) {
    }

    public function uploadPublic(RegistrationFileUploadRequest $request)
    {
        $data = $request->validated();
        $event = Event::query()->findOrFail((int) $data['event_id']);

        $registrationFile = $this->registrationFileService->uploadTemporary(
            $event,
            $data['email'],
            $data['field_key'],
            $request->file('file'),
            'public_registration',
            null,
        );

        return $this->responseSuccess([
            'file_id' => $registrationFile->file_id,
            'event_id' => $registrationFile->event_id,
            'field_key' => $registrationFile->field_key,
            'name' => $registrationFile->original_name,
            'mime_type' => $registrationFile->mime_type,
            'size_bytes' => $registrationFile->size_bytes,
            'status' => $registrationFile->status,
            'expires_at' => optional($registrationFile->expires_at)?->toISOString(),
        ], 'Registration file uploaded');
    }

    public function uploadPortal(PortalRegistrationFileUploadRequest $request)
    {
        $data = $request->validated();

        $portalUser = $this->portalSessionService->authenticate(
            $data['email'],
            $data['login_token'],
        );

        $event = Event::query()->findOrFail((int) $data['event_id']);

        $portalUser->registrations()
            ->where('event_id', $event->id)
            ->firstOrFail();

        $registrationFile = $this->registrationFileService->uploadTemporary(
            $event,
            $portalUser->email,
            $data['field_key'],
            $request->file('file'),
            'portal_profile_update',
            $portalUser,
        );

        return $this->responseSuccess([
            'file_id' => $registrationFile->file_id,
            'event_id' => $registrationFile->event_id,
            'field_key' => $registrationFile->field_key,
            'name' => $registrationFile->original_name,
            'mime_type' => $registrationFile->mime_type,
            'size_bytes' => $registrationFile->size_bytes,
            'status' => $registrationFile->status,
            'expires_at' => optional($registrationFile->expires_at)?->toISOString(),
        ], 'Portal file uploaded');
    }

    public function downloadPortal(string $fileId, PortalFileDownloadRequest $request)
    {
        $data = $request->validated();

        $portalUser = $this->portalSessionService->authenticate(
            $data['email'],
            $data['login_token'],
        );

        $registrationFile = $this->registrationFileService->authorizePortalDownload(
            $portalUser,
            (int) $data['event_id'],
            $fileId,
        );

        $this->registrationFileService->markDownloaded($registrationFile, $portalUser);

        return $this->registrationFileService->streamDownloadResponse($registrationFile);
    }
}
