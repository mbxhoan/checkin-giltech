<?php

namespace App\Http\Requests\Api\Videc;

use App\Http\Requests\BaseFormRequest;

class RegistrationFileUploadRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->filled('email') && is_string($this->input('email'))) {
            $data['email'] = mb_strtolower(trim($this->input('email')));
        }

        if ($this->filled('field_key') && is_string($this->input('field_key'))) {
            $data['field_key'] = trim($this->input('field_key'));
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        $maxSizeKb = max(1, (int) config('registration_files.max_size_kb', 2048));
        $allowedExtensions = implode(',', (array) config('registration_files.allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png']));
        $allowedMimeTypes = implode(',', (array) config('registration_files.allowed_mime_types', ['application/pdf', 'image/jpeg', 'image/png']));

        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'email' => ['required', 'email'],
            'field_key' => ['required', 'string', 'max:100'],
            'file' => [
                'required',
                'file',
                "max:{$maxSizeKb}",
                "mimes:{$allowedExtensions}",
                "mimetypes:{$allowedMimeTypes}",
            ],
        ];
    }
}
