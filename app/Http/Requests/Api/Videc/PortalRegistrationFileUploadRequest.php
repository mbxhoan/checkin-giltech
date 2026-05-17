<?php

namespace App\Http\Requests\Api\Videc;

class PortalRegistrationFileUploadRequest extends RegistrationFileUploadRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'login_token' => ['required', 'string', 'min:20'],
        ]);
    }
}
