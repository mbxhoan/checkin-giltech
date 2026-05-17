<?php

namespace App\Http\Requests\Web\Users;

use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'company_name'  => [
                'required',
                'string',
                'max:50',
                'unique:companys,name',
            ],
            'name'          => [
                'required',
                'string',
                'max:255',
            ],
            'email'         => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password'      => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])[A-Za-z\d\W_]{8,}$/',
                'not_regex:/[À-ỹà-ỹ]/u', // block Vietnamese characters (accents)
            ],
            'phone'         => [
                'required',
                'regex:/^[0-9+\-\s_.()]{9,15}$/',
                'unique:'.User::class,
            ],
            'package'       => [
                'required',
                'string',
                'max:50',
                'exists:packages,code',
                // Rule::in(array_keys(config('info.packages')))
            ],
            'position'      => [
                'nullable',
                'string',
                'max:200',
            ],
            'company_type'  => [
                'nullable',
                'string',
                'max:50',
            ],
            'devices'       => [
                'nullable',
                'array',
                Rule::in(array_keys(config('info.devices')))
            ]
        ];
    }

    public function attributes()
    {
        return [
            'company_name'  => 'Tên công ty',
            'name'          => 'Họ tên',
            'password'      => 'Mật khẩu',
            'password_confirmation'      => 'Xác nhận mật khẩu',
            'package'       => 'Gói sử dụng',
            'position'      => 'Chức vụ',
            'company_type'  => 'Loại hình sự kiện',
            'devices'       => 'Thiết bị thuê',
        ];
    }

    public function messages()
    {
        return [
            'password.regex'        => 'Mật khẩu phải chứa ít nhất 1 chữ cái viết hoa, 1 chữ cái viết thường và 1 ký hiệu.',
            'password.not_regex'    => 'Mật khẩu không được chứa ký tự tiếng Việt.',
        ];
    }
}
