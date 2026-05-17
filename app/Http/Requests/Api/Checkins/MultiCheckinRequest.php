<?php

namespace App\Http\Requests\Api\Checkins;

use App\Http\Requests\BaseFormRequest;
use App\Rules\WithoutSpace;

class MultiCheckinRequest extends BaseFormRequest
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
        $userAgent = $this->header('user-agent');

        if ($userAgent === "WebPortal") {
            $this->merge(['user_group' => 'DESKTOP']);
        } else if (in_array($userAgent, [
            'PDA',
            'MobileApp',
        ])) {
            $this->merge(['user_group' => 'MOBILE']);
        }

        return [
            'event_code' => [
                'required',
                'string',
                'max:200',
                'exists:events,code'
            ],
            'total_records' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'data' => [
                'required',
                'array',
            ],
            'data.*.qrcode' => [
                'required',
                'string',
                'max:200',
                'regex:/^[a-zA-Z0-9-+_#$*%]+$/',
                new WithoutSpace
            ],
            'data.*.scan_time' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'event_code'        => 'Mã sự kiện',
            'total_records'     => 'Số lượng record',
            'data'              => 'Dữ liệu checkin',
            'data.*.qrcode'     => 'Qrcode',
            'data.*.scan_time'  => "Thời gian checkin",
        ];
    }
}
