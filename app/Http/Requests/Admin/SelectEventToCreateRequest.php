<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SelectEventToCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_id'          => [
                'required',
                'integer',
                'exists:events,id',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'event_id'          => "Sự kiện",
        ];
    }
}
