<?php

namespace App\Http\Requests\Api\Clients;

use App\Http\Requests\BaseFormRequest;

class GenerateQrcodeOnSettingRequest extends BaseFormRequest
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
            'name'              => [
                'required',
                'string',
                'max:255'
            ],
            'email'             => [
                'nullable',
                'email',
                'max:255',
                // 'lowercase'
            ],
            'custom_fields'    => 'nullable|array',
        ];
    }

    public function attributes()
    {
        return [
            'name'          => "Họ tên",
            'email'         => "Email",
            'custom_fields' => "Trường thông tin",
        ];
    }
}
