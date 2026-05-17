<?php

namespace App\Http\Requests\Api\Videc;

use App\Http\Requests\BaseFormRequest;

class PortalProfileUpdateRequest extends BaseFormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
