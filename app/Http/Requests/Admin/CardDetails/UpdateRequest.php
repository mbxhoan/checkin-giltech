<?php

namespace App\Http\Requests\Admin\CardDetails;

use App\Models\CardDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'field'     => [
                'required',
                'string',
                'max:50',
            ],
            /* boolean thì nên để nullable */
            'is_show'   => [
                'nullable',
                'boolean',
            ],
            'color'     => [
                'nullable',
                'string',
                'max:50',
            ],
            'font'      => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys(CardDetail::getFonts())),
            ],
            'font_size' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'h_align'   => [
                'required',
                'string',
                'max:50',
                Rule::in(array_keys(CardDetail::getHAligns())),
            ],
            'pos_x'     => [
                'required',
                'numeric',
            ],
            'pos_y'     => [
                'required',
                'numeric',
            ],
            'width'     => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'height'    => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'field'     => 'Trường thông tin',
            'is_show'   => 'Hiển thị',
            'color'     => 'Màu chữ',
            'font'      => 'Font',
            'font_size' => 'Cỡ chữ',
            'align'     => 'Canh',
            'pos_x'     => 'Canh ngang',
            'pos_y'     => 'Canh dọc',
        ];
    }
}
