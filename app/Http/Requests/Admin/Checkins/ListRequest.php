<?php

namespace App\Http\Requests\Admin\Checkins;

use App\Models\Checkin;
use App\Models\Client;
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
            'unique_qrcode'         => [
                'nullable',
                'boolean',
            ],
            'status'                => [
                'nullable',
                'max:50',
                Rule::in(array_keys(Checkin::STATUES)), // Validate against allowed statuses
            ],
            'type'                  => [
                'nullable',
                'max:50',
                Rule::in(array_keys(Checkin::TYPES)),
            ],
            'field_date'            => [
                'nullable',
                'max:50',
                Rule::in(array_keys(Checkin::getDateFields())), // Validate against allowed statuses
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
