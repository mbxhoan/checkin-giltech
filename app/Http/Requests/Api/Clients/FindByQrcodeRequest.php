<?php

namespace App\Http\Requests\Api\Clients;

use App\Http\Requests\BaseFormRequest;

class FindByQrcodeRequest extends BaseFormRequest
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
            'event_id'          => 'required|integer|exists:events,id',
            'qrcode'            => [
                'required',
                'string',
                'max:200',
                'exists:clients,qrcode'
            ],
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
