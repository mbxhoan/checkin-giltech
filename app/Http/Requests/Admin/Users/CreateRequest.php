<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

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
        if (!auth()->user()->isSysAdmin()) {
            $this->merge(['company_id' => auth()->user()->company_id]);
        }

        $rules = [
            'is_checkout'   => 'nullable|boolean',
            'package_id'    => 'nullable|integer|exists:packages,id',
            'company_id'    => 'nullable|integer|exists:companys,id',
            'event_id'      => 'nullable|integer|exists:events,id',
            'name'          => [
                'required',
                'string',
                'max:255',
            ],
            'username'      => [
                'required',
                // 'lowercase',
                'unique:users,username,' . (optional($this->user)->id ?: 'NULL'),
                'regex:/^[a-zA-Z0-9\-_.]+$/',
            ],
            'email'         => 'required|email|lowercase|unique:users,email,' . (optional($this->user)->id ?: 'NULL'),
            'password'      => 'required|confirmed',
            'roles'         => 'required|array',
            'roles.*'       => 'required|exists:roles,id',
            'gender'        => 'nullable|string',
            'status'        => [
                'required',
                Rule::in(array_keys(User::STATUES)),
            ],
            'expire_date'   => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];

        if (!auth()->user()->isSysAdmin()) {
            // $rules['event_id'] = 'required|integer|exists:events,id';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('company_id')) {
                if (!$this->service->ensureLimited($this->input('company_id'), 'limited_users')) {
                    $validator->errors()->add('company_id', 'ĐÃ VƯỢT QUÁ SỐ LƯỢNG TÀI KHOẢN CHO PHÉP');
                }
            }
        });
    }
}
