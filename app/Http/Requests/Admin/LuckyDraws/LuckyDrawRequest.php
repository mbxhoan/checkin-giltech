<?php

namespace App\Http\Requests\Admin\LuckyDraws;

use App\Models\Label;
use App\Models\LuckyDraw;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LuckyDrawRequest extends FormRequest
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

        return [
            'event_id'      => 'required|integer|exists:events,id',
            'name'          => [
                'required',
                'string',
                'max:50'
            ],
            'background_url_mobile'             =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'background_url_desktop'            =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'type'                  => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys(LuckyDraw::TYPES)),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'background_url_mobile'                 => 'Ảnh nền màn hình điện thoại',
            'background_url_desktop'                => 'Ảnh nền màn hình máy tính',
        ];
    }
}
