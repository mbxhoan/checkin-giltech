<?php

namespace App\Http\Requests\Admin\CardDetails;

use App\Models\CardDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'card_id'       => 'required|integer|exists:cards,id',
            'card_code'     => 'required|string|exists:cards,code',
            'field'         => [
                'required',
                'string',
                'max:50'
            ],
            'type'          => [
                'nullable',
                Rule::in(array_keys(CardDetail::getTypes())),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'card_id'               => 'Mã thư mời',
        ];
    }
}
