<?php

namespace App\Http\Requests\Admin\LabelDetails;

use App\Models\LabelDetail;
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
            'label_id'      => 'required|integer|exists:labels,id',
            'field'         => [
                'required',
                'string',
                'max:50'
            ],
            'type'          => [
                'nullable',
                Rule::in(array_keys(LabelDetail::getTypes())),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'label_id'               => 'Mã tem',
        ];
    }
}
