<?php

namespace App\Http\Requests\Admin\Labels;

use App\Models\LandingPage;
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
            'event_id'              => 'nullable|integer|exists:companys,id',
            'status'                => [
                'nullable',
                'max:50',
                Rule::in(array_keys(LandingPage::STATUES)), // Validate against allowed statuses
            ],
            'field_date'            => [
                'nullable',
                'max:50',
                Rule::in(array_keys(LandingPage::getDateFields())), // Validate against allowed statuses
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
