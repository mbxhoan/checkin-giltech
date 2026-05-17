<?php

namespace App\Http\Requests\Admin;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabelsRequest extends FormRequest
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
            'event_id'      => 'required|integer|exists:events,id',
            'name'          => [
                'required',
                'string',
                'max:50'
            ],
            'width'                 => [
                'required',
                'numeric',
                'min:1',
                'max:50',
            ],
            'height'                => [
                'required',
                'numeric',
                'min:1',
                'max:50',
            ],
            'unit'                  => [
                'required',
                'string',
                'max:10',
            ],
            'type'                  => [
                'nullable',
                'string',
                'max:50',
            ],
            'status'                => [
                'required',
                'max:50',
                Rule::in(array_keys(Label::STATUES)), // Validate against allowed statuses
            ],
        ];
    }

    public function attributes()
    {
        return [
            'width'                 => 'Chiều dài',
            'height'                => 'Chiều cao',
            'unit'                  => 'Đơn vị',
        ];
    }
}
