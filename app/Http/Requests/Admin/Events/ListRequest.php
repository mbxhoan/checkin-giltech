<?php

namespace App\Http\Requests\Admin\Events;

use App\Models\Event;
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
            'company_id'            => 'nullable|integer|exists:companys,id',
            'province_id'           => 'nullable|integer|exists:provinces,id',
            'status'                => [
                'nullable',
                'max:50',
                Rule::in(array_keys(Event::STATUES)), // Validate against allowed statuses
            ],
            'field_date'            => [
                'nullable',
                'max:50',
                Rule::in(array_keys(Event::getDateFields())), // Validate against allowed statuses
            ],
            'from_date'             => [
                'nullable',
                'date',
            ],
            'to_date'               => [
                'nullable',
                'date',
                'after_or_equal:from_date'
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
