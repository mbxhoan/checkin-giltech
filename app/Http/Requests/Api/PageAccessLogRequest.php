<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class PageAccessLogRequest extends BaseFormRequest
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
            'lp_id'         => 'nullable|integer|exists:landing_pages,id',
            'page'          => 'nullable|string',
            // 'ip_address'    => 'nullable|string',
            'user_id'       => 'nullable|integer|exists:users,id'
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
