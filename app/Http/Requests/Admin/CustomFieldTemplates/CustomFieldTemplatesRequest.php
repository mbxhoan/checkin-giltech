<?php

namespace App\Http\Requests\Admin\CustomFieldTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomFieldTemplatesRequest extends FormRequest
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
                'nullable',
                'lowercase',
                'string',
                'max:50',
                'regex:/^[A-Za-z_]+$/',
                // Rule::notIn(['type', 'Type', 'TYPE']),
                Rule::unique('custom_field_templates', 'name') // Check uniqueness against the 'name' column
                    ->where('event_id', $this->input('event_id'))
                    ->ignore($this->route()->parameter('custom_field_template')),
            ],
            // 'order'         => 'required|integer|min:1|',
            'description'   => 'nullable|string|max:255',
            'checkins'      => [
                'nullable',
                'array',
            ],
            'checkins.*'    => [
                'nullable',
                'max:50',
                // Rule::in([
                //     "mobile",
                //     "desktop"
                // ]),
            ],
            'accepts'      => [
                'nullable',
                'array',
            ],
            'accepts.*'    => [
                'nullable',
                'max:50',
            ],
            'options.*.key'   => [
                'nullable',
                'string',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'options.*.key'      => "Key của lựa chọn",
        ];
    }
}
