<?php

namespace App\Http\Requests\Admin\Events;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomCheckinMessageRequest extends FormRequest
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
            'custom_checkin_messages.*'               => [
                'required',
                'array',
            ],
            'custom_checkin_messages.*.*'             => [
                'required',
                'array',
            ],
            'custom_checkin_messages.*.*.msg'         => [
                'required',
                'max:50',
            ],
            'custom_checkin_messages.*.*.show_info'   => [
                'nullable',
                'boolean',
            ],
            'custom_checkin_messages.*.*.bold'        => [
                'nullable',
                'boolean',
            ],
            'custom_checkin_messages.*.*.italic'      => [
                'nullable',
                'boolean',
            ],
            'custom_checkin_messages.*.*.bg'          => [
                'nullable',
                'boolean',
            ],
            'custom_checkin_messages.*.*.color'       => [
                'nullable',
                'max:20',
            ],
            'custom_checkin_messages.*.*.bg_color'    => [
                'nullable',
                'max:20',
            ],
            'custom_checkin_messages.*.*.font_size'   => [
                'nullable',
                'numeric',
            ],
            'custom_checkin_messages.*.*.font'        => [
                'nullable',
                'string',
            ],
            'custom_checkin_messages.*.*.align'       => [
                'nullable',
                'string',
            ],
            'custom_checkin_messages.*.*.width'       => [
                'nullable',
                'numeric',
            ],
            'custom_checkin_messages.*.*.pos_x'       => [
                'nullable',
                'numeric',
            ],
            'custom_checkin_messages.*.*.pos_y'       => [
                'nullable',
                'numeric',
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
