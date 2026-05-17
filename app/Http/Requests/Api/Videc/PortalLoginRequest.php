<?php

namespace App\Http\Requests\Api\Videc;

use App\Http\Requests\BaseFormRequest;

class PortalLoginRequest extends BaseFormRequest
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
            'password' => ['nullable', 'string'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ];
    }
}
