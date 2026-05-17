<?php

namespace App\Http\Requests\Admin\CustomFieldTemplates;

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
            'new'               => 'required|array',
            'new.event_id'      => 'required|integer|exists:events,id',
            'new.name'     => [
                'required',
                'lowercase',
                'string',
                'max:255',
                'regex:/^[A-Za-z_]+$/',
                // Rule::notIn(['type', 'Type', 'TYPE']),
                Rule::unique('custom_field_templates', 'name') // Check uniqueness against the 'name' column
                    ->where('event_id', $this->input('new.event_id'))
                    ->ignore($this->route()->parameter('custom_field_template')),
            ],
            'new.order'         => 'required|integer|min:1|',
            'new.description'   => 'nullable|string|max:255',
            'new.checkins'      => [
                'nullable',
                'array',
            ],
            'new.checkins.*'    => [
                'nullable',
                // 'string',
                'max:50',
                // Rule::in([
                //     "mobile",
                //     "desktop"
                // ]),
            ],
            'new.accepts'      => [
                'nullable',
                'array',
            ],
            'new.accepts.*'    => [
                'nullable',
                'max:50',
            ],
            'new.options.*.key' => [
                'nullable',
                'string',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'new.event_id'      => "Sự kiện",
            'new.name'          => "Tên trường thông tin",
            'new.order'         => "Thứ tự",
            'new.description'   => "Mô tả thông tin",
            'new.options.*.key' => "Key của lựa chọn",
        ];
    }
}
