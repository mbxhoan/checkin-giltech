<?php

namespace App\Http\Requests\Admin\LuckyDrawClients;

use App\Models\LuckyDraw;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncRequest extends FormRequest
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
            'group'      => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    "all",
                    "checked"
                ]),
            ],
            'client_type' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'group'                 => 'Lọc khách',
            'client_type'           => 'Nhóm khách',
        ];
    }
}
