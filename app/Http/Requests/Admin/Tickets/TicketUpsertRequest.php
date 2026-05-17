<?php

namespace App\Http\Requests\Admin\Tickets;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TicketUpsertRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['metadata_json'] as $field) {
            if ($this->filled($field) && is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    public function rules(): array
    {
        $ticket = $this->route('ticket');
        $uniqueCode = Rule::unique('tickets', 'code')
            ->where(fn ($query) => $query->where('event_code', $this->input('event_code')));

        if (is_numeric($ticket)) {
            $uniqueCode->ignore((int) $ticket);
        }

        return [
            'event_code' => ['required', 'string', 'max:200', Rule::exists('events', 'code')],
            'code' => [
                'required',
                'string',
                'max:200',
                $uniqueCode,
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'dates_string' => ['nullable', 'string', 'max:200'],
            'dates_valid_from' => ['nullable', 'date'],
            'dates_valid_to' => ['nullable', 'date', 'after_or_equal:dates_valid_from'],
            'group_code' => ['nullable', 'string', 'max:100'],
            'group_label_vi' => ['nullable', 'string', 'max:255'],
            'group_label_en' => ['nullable', 'string', 'max:255'],
            'group_allow_none' => ['nullable', 'boolean'],
            'group_none_label_vi' => ['nullable', 'string', 'max:255'],
            'group_none_label_en' => ['nullable', 'string', 'max:255'],
            'display_name_vi' => ['nullable', 'string', 'max:255'],
            'display_name_en' => ['nullable', 'string', 'max:255'],
            'display_price_usd' => ['nullable', 'numeric', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'min:1'],
            'metadata_json' => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'event_code' => 'Mã sự kiện',
            'code' => 'Mã vé',
            'name' => 'Tên vé',
            'type' => 'Loại vé',
            'sort_order' => 'Thứ tự',
            'price' => 'Giá VND',
            'dates_string' => 'Ngày checkin',
            'dates_valid_from' => 'Ngày bắt đầu',
            'dates_valid_to' => 'Ngày kết thúc',
            'group_code' => 'Mã nhóm',
            'group_label_vi' => 'Tên nhóm tiếng Việt',
            'group_label_en' => 'Tên nhóm tiếng Anh',
            'group_allow_none' => 'Cho phép không tham gia',
            'group_none_label_vi' => 'Nhãn không tham gia tiếng Việt',
            'group_none_label_en' => 'Nhãn không tham gia tiếng Anh',
            'display_name_vi' => 'Tên hiển thị tiếng Việt',
            'display_name_en' => 'Tên hiển thị tiếng Anh',
            'display_price_usd' => 'Giá USD',
            'max_quantity' => 'Số lượng tối đa',
            'metadata_json' => 'Metadata JSON',
        ];
    }
}
