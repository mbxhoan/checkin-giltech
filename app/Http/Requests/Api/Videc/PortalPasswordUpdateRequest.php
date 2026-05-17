<?php

namespace App\Http\Requests\Api\Videc;

use App\Http\Requests\BaseFormRequest;

class PortalPasswordUpdateRequest extends BaseFormRequest
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
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
