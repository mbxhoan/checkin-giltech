<?php

namespace App\Http\Requests\Admin\Labels;

use Illuminate\Foundation\Http\FormRequest;

class CloneRequest extends FormRequest
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
            'name'          => [
                'required',
                'string',
                'max:50'
            ],
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
