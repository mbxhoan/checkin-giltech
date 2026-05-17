<?php

namespace App\Http\Requests\Admin;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardsRequest extends FormRequest
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
        $allowTypes = config('app.upload_media_allow_types');
        $allowSize = config('app.upload_media_size_max');
        $allowSize = 5000;

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
                Rule::in(array_keys(Card::STATUES)), // Validate against allowed statuses
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
