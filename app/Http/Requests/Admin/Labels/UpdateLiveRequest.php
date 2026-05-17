<?php

namespace App\Http\Requests\Admin\Labels;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLiveRequest extends FormRequest
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
            'name'          => [
                'required',
                'string',
                'max:50'
            ],
            'width'                 => [
                'required',
                'numeric',
                'min:0.1', // or min:1 if you want at least 1
                'max:50',
            ],
            'height'                => [
                'required',
                'numeric',
                'min:0.1', // or min:1 if you want at least 1
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
