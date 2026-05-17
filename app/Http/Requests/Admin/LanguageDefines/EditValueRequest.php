<?php

namespace App\Http\Requests\Admin\LanguageDefines;

use Illuminate\Foundation\Http\FormRequest;

class EditValueRequest extends FormRequest
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
            'language_id'   => 'required|integer|exists:languages,id',
            // 'lang_code'     => 'required|string|max:50|exists:languages,code',
            'name'          => [
                'required',
                'regex:/^[a-zA-Z0-9-+_#.$*%]+$/',
                'max:50',
            ],
            'value'         =>  [
                'nullable',
                // 'max:255',
            ],
            'customs'       => [
                'nullable',
                'array',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'value' => 'Nội dung',
        ];
    }
}
