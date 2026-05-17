<?php

namespace App\Http\Requests\Admin\Campaigns;

use App\Models\Campaign;
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
                Rule::in(array_keys(Campaign::STATUES)), // Validate against allowed statuses
            ],
            'type'                  => [
                'nullable',
                'max:50',
            ],
            // 'field_date'            => [
            //     'nullable',
            //     'max:50',
            //     Rule::in(array_keys(Campaign::getDateFields())), // Validate against allowed statuses
            // ],
            'from_date'             => [
                'nullable',
                'date',
            ],
            'to_date'               => [
                'nullable',
                'date',
            ],
            'checked_in'            => [
                'nullable',
                'boolean',
            ]
        ];
    }

    public function attributes()
    {
        return [];
    }
}
