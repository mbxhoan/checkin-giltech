<?php

namespace App\Http\Requests\Api\Clients;

use App\Http\Requests\BaseFormRequest;

class FindRequest extends BaseFormRequest
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
            'event_id'          => 'nullable|integer|exists:events,id',
            'event_code'        => 'nullable|integer|exists:events,code',
            'qrcode'            => [
                'nullable',
                'string',
                'max:200',
                'exists:clients,qrcode'
            ],
            'id'            => [
                'nullable',
                'integer',
                'exists:clients,id'
            ],
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
