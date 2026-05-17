<?php

namespace App\Http\Requests\Admin\CardDetails;

use App\Models\Card;
use App\Models\LandingPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class _UpdateByCustomFieldTemplatesRequest extends FormRequest
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
            'event_id'      => 'required|integer|exists:events,id',
            'event_code'    => 'required|string|exists:events,code',
            'code'          => [
                'required',
                'string',
                // 'regex:/^[a-z0-9-_]+$/', // only lowercase, numbers, dashes, underscores
                // 'unique:cards,code,' . (optional($this->card)->id ?: 'NULL'),
                'max:50'
            ],
            'file_name_template'    => [
                'nullable',
                'string',
                'max:50',
            ],
            'extension'             => [
                'nullable',
                Rule::in(array_keys(Card::EXTENSIONS)),
            ],
            'background'            =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'client_type'           => [
                'nullable',
                'string',
                'max:50',
            ],
            'status'                => [
                'required',
                'max:50',
                Rule::in(array_keys(LandingPage::STATUES)), // Validate against allowed statuses
            ],
        ];
    }

    public function attributes()
    {
        return [
            'code'                  => 'Thông tin',
            'file_name'             => 'Tên file',
            'background'            => 'Background',
        ];
    }
}
