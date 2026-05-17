<?php

namespace App\Http\Requests\Admin\Campaigns;

use App\Models\Campaign;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'event_id'          => [
                'required',
                'integer',
                'exists:events,id',
            ],
            'template_id'       => [
                'required',
                'integer',
            ],
            'name'              => [
                'nullable',
                'string',
                'max:255',
            ],
            'type'              => [
                'nullable',
                'string',
                'max:50',
            ],
            'subject'           => [
                'nullable',
                'string',
                'max:50',
            ],
            'from_email'        => [
                'required',
                'email',
                'max:50',
                // Rule::in([
                //     'admin@delfi.vn'
                // ]),
            ],
            'from_name'         => [
                'nullable',
                'string',
                'max:50',
            ],
            'status'            => [
                'nullable',
                'string',
                Rule::in(array_keys(Campaign::STATUES)),
            ],
            'cc'                => [
                'nullable',
                'string',
                'max:255',
            ],
            'bcc'               => [
                'nullable',
                'string',
                'max:255',
            ],
            'message_stream'    => [
                'nullable',
                'string',
                'max:50',
            ],
            'limitation_per_time' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'hold_time'         => [
                'nullable',
                'integer',
                'min:0',
            ],
            'fixed_attachments' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'event_id'          => "Sự kiện",
            'template_id'       => "Nội dung email",
            'name'              => "Tên chiến dịch",
            'type'              => "Nhóm khách",
            'subject'           => "Tiêu đề",
            'from_email'        => "Email gửi đi",
            'from_name'         => "Tên người gửi",
            'cc'                => "CC",
            'bcc'               => "BCC",
            'message_stream'    => "Message stream",
            'limitation_per_time' => "Giới hạn gửi trong 1 lần",
            'hold_time'         => "Thời gian giữ lại",
            'fixed_attachments' => "Tệp đính kèm cố định",
        ];
    }
}
