<?php

namespace App\Http\Requests\Admin\LuckyDrawRewards;

use Illuminate\Foundation\Http\FormRequest;

class LuckyDrawRewardRequest extends FormRequest
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
            'reward'                => [
                'required',
                'array',
            ],
            'reward.code'           => [
                'required',
                'string',
                'max:50',
                'unique:lucky_draw_rewards,code,' . (optional($this->lucky_draw_reward)->id ?: 'NULL'),
            ],
            'reward.name'           => [
                'required',
                'string',
                'max:200',
            ],
            'reward.value'          => [
                'required',
                'string',
                'max:50',
            ],
            'reward.img_link'       => [
                'required',
                'string',
                'max:255',
            ],
            'reward.order'          => [
                'required',
                'numeric',
                'min:0',
                'max:50',
            ],
            'reward.order_name'     => [
                'required',
                'string',
                'max:50',
            ],
            'reward.time'           => [
                'required',
                'numeric',
                'min:1',
                'max:50',
            ],
            'reward.probability'    => [
                'required',
                'numeric',
                'min:1',
                'max:99',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'reward.code'           => 'Mã giải',
            'reward.name'           => 'Tủ lạnh Panasonic/TV LG 8K',
            'reward.value'          => 'Giá trị',
            'reward.img_link'       => 'Link hình ảnh',
            'reward.number'         => 'Thứ tự giải',
            'reward.order_name'     => 'Tên cho thứ tự giải',
            'reward.time'           => 'Thời gian quay',
            'reward.probability'    => 'Xác suất trúng',
        ];
    }
}
