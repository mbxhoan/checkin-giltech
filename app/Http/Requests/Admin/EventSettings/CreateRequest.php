<?php

namespace App\Http\Requests\Admin\EventSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'event_id'      => 'required|integer|exists:events,id',
            'name'          => [
                'required',
                'string',
                'max:255',
                Rule::unique('event_settingss', 'name') // Check uniqueness against the 'name' column
                    ->where('event_id', $this->input('event_id'))
                    ->ignore($this->route()->parameter('event_settings')),
            ],
            'description'   => 'nullable|string|max:255',
            ''              => ''
        ];
    }

    public function attributes()
    {
        return [
            'event_id'      => "Sự kiện",
        ];
    }
}
