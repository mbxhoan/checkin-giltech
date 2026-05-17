<?php

namespace App\Http\Requests\Admin\CustomFieldTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'exist'               => 'required|array',
            'exist.event_id'      => 'required|integer|exists:events,id',
            'exist.name'     => [
                'nullable',
                'lowercase',
                'string',
                'max:255',
                'regex:/^[A-Za-z_]+$/',
                Rule::unique('custom_field_templates', 'name') // Check uniqueness against the 'name' column
                    ->where('event_id', $this->input('exist.event_id'))
                    ->ignore($this->route()->parameter('custom_field_template')),
            ],
            'exist.order'         => 'required|integer|min:1|',
            'exist.description'   => 'nullable|string|max:255',
            'exist.checkins'      => [
                'nullable',
                'array',
            ],
            'exist.checkins.*'    => [
                'nullable',
                // 'string',
                'max:50',
                // Rule::in([
                //     "mobile",
                //     "desktop"
                // ]),
            ],
            'exist.accepts'      => [
                'nullable',
                'array',
            ],
            'exist.accepts.*'    => [
                'nullable',
                'max:50',
            ],
            'exist.options.*.key' => [
                'nullable',
                'string',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'exist.event_id'      => "Sự kiện",
            'exist.name'          => "Tên trường thông tin",
            'exist.order'         => "Thứ tự",
            'exist.description'   => "Mô tả thông tin",
            'exist.options.*.key' => "Key của lựa chọn",
        ];
    }
}
