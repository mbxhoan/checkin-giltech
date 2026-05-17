<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanysRequest extends FormRequest
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
            'code'              => [
                'required',
                'unique:companys,code,' . (optional($this->company)->id ?: 'NULL'),
            ],
            'name'              => 'required|string|max:255',
            'status'            => [
                'required',
                Rule::in(array_keys(Company::STATUES)), // Validate against allowed statuses
            ],
            'languages'         => [
                'required',
                'array',
            ],
            'settings'          => [
                'nullable',
                'array',
            ],
            'limited_events'    => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000',
            ],
            'limited_users'     => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000',
            ],
            'limited_campaigns' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000',
            ],
            'limited_emails'    => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'limited_clients'   => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'limited_events'    => 'Giới hạn sự kiện',
            'limited_users'     => 'Giới hạn tài khoản',
            'limited_emails'    => 'Giới hạn emails',
            'limited_campaigns' => 'Giới hạn campaigns',
            'limited_clients'   => 'Giới hạn dữ liệu',
        ];
    }
}
