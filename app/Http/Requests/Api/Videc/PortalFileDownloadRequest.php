<?php

namespace App\Http\Requests\Api\Videc;

use App\Http\Requests\BaseFormRequest;

class PortalFileDownloadRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('email') && is_string($this->input('email'))) {
            $this->merge([
                'email' => mb_strtolower(trim($this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'login_token' => ['required', 'string', 'min:20'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ];
    }
}
