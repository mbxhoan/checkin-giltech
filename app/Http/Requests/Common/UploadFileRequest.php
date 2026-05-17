<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
        $allowTypes = config('app.upload_data_allow_types');
        $allowSize = config('app.upload_data_size_max');

        return [
            'file'          => "required|mimes:{$allowTypes}|max:{$allowSize}",
        ];
    }
}
