<?php

namespace App\Http\Requests\Admin\Events;

use Illuminate\Foundation\Http\FormRequest;

class RemoveFeatureRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

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
            'feature' => [
                'required',
                'string',
                'max:50',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'feature' => 'Tính năng',
        ];
    }
}
