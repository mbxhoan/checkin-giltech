<?php

namespace App\Http\Requests\Admin\PromoCodes;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class PromoCodeUpsertRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promoCode = $this->route('promoCode');
        $eventId = $this->input('event_id');

        $uniqueCode = Rule::unique('promo_codes', 'code')
            ->where(fn ($query) => $query->where('event_id', $eventId));

        if (is_numeric($promoCode)) {
            $uniqueCode->ignore((int) $promoCode);
        }

        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'code' => ['required', 'string', 'max:100', $uniqueCode],
            'discount_type' => ['nullable', 'string', Rule::in(['percentage'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
            'metadata_json' => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'event_id' => 'Sự kiện',
            'code' => 'Mã promo',
            'discount_type' => 'Kiểu giảm giá',
            'discount_value' => 'Giá trị giảm',
            'max_discount_amount' => 'Giảm tối đa',
            'min_order_amount' => 'Đơn hàng tối thiểu',
            'usage_limit' => 'Số lượt dùng',
            'starts_at' => 'Ngày bắt đầu',
            'ends_at' => 'Ngày kết thúc',
            'status' => 'Trạng thái',
            'metadata_json' => 'Metadata JSON',
        ];
    }
}
