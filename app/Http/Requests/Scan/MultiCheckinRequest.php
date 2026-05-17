<?php

namespace App\Http\Requests\Scan;

use App\Http\Requests\BaseFormRequest;

class MultiCheckinRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'event_code'        => ['required', 'string', 'max:200'],
            'total_records'     => ['required', 'integer'],
            'data'              => ['required', 'array'],
            'data.*.qrcode'     => ['required', 'string', 'max:200'],
        ];
    }
}
