<?php

namespace App\Http\Requests\Admin\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status'                => [
                'nullable',
                'max:50',
                Rule::in(array_keys(EmailTemplate::STATUES)), // Validate against allowed statuses
            ],
            'type'                  => [
                'nullable',
                'max:50',
            ],
            'layout'                => [
                'nullable',
                'max:50',
            ],
            'limit'                 => [
                'nullable',
                'numeric',
                'min:1',
                'max:50',
            ],
            'from_date'             => [
                'nullable',
                'date',
            ],
            'to_date'               => [
                'nullable',
                'date',
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
