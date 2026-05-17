<?php

namespace App\Http\Requests\Admin\Checkins;

use App\Http\Requests\BaseFormRequest;
use App\Models\Checkin;
use App\Rules\WithoutSpace;
use Illuminate\Foundation\Http\FormRequest;

class CheckinRequest extends FormRequest
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
            'event_code' => [
                'required',
                'string',
                'max:200',
                'exists:events,code'
            ],
            'qrcode' => [
                'required',
                'string',
                'max:200',
                // 'exists:clients,qrcode',
                new WithoutSpace
            ],
            'type' => [
                'nullable',
                'string',
                'in:'.Checkin::TYPE_CHECKIN.','.Checkin::TYPE_CHECKOUT,
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }
}
