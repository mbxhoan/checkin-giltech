<?php

namespace App\Http\Requests\Admin\Checkins;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewConfigRequest extends FormRequest
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
            'screen' => [
                'nullable',
                'string',
                'max:50',
                Rule::in([
                    "mobile",
                    "desktop"
                ]),
            ],
            'msg' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys([
                    'none'              => [],
                    'success'           => [
                        "msg"           => __('responses.checkin.success'), // hoặc số lần checkin
                        "showInfo"      => true,
                    ],
                    'failed'            => [
                        "msg"           => __('responses.checkin.errors.no_data_found'),
                        "showInfo"      => false,
                    ],
                    'duplicated'        => [
                        "msg"           => __('responses.checkin.errors.duplicate_checkin'),
                        "showInfo"      => true,
                    ],
                ])),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'screen'    => 'Màn hình',
            'msg'       => 'Thông báo',
        ];
    }
}
