<?php

namespace App\Http\Requests\Admin\Cards;

use App\Models\Card;
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
                Rule::in(array_keys(Card::STATUES)), // Validate against allowed statuses
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
