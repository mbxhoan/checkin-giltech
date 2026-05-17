<?php

namespace App\Http\Requests\Admin\LabelDetails;

use App\Models\LabelDetail;
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
                // Rule::in(array_keys(LabelDetail::getFonts())),
            ],
            'size' => [
                'nullable',
                'numeric',
                'min:1',
            ],
            'h_align'   => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys(LabelDetail::getHAligns())),
            ],
            'v_align'   => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys(LabelDetail::getVAligns())),
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
            'v_align'   => 'Canh dọc',
            'h_align'   => 'Canh ngang',
            'align'     => 'Canh',
            'align'     => 'Canh',
            'pos_x'     => 'Canh ngang',
            'pos_y'     => 'Canh dọc',
        ];
    }
}
