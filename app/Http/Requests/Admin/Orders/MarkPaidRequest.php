<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class MarkPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'receipt_code' => ['nullable', 'string', 'max:100'],
        ];
    }
}
