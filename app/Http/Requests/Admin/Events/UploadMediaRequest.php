<?php

namespace App\Http\Requests\Admin\Events;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
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
        $allowAudioTypes = config('app.upload_audio_allow_types');
        $allowSize = config('app.upload_media_size_max');

        return [
            'logo'                     => "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'main_bg_desktop'          => "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'main_bg_mobile'           => "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'sound_success'            => "nullable|file|mimes:{$allowAudioTypes}|max:{$allowSize}",
            'sound_fail'               => "nullable|file|mimes:{$allowAudioTypes}|max:{$allowSize}",
        ];
    }

    public function attributes()
    {
        return [
            'logo'                      => "Logo",
            'main_bg_desktop'           => "Background cho desktop",
            'main_bg_mobile'            => "Background cho mobile",
            'sound_success'             => "Tiếng thành công",
            'sound_fail'                => "Tiếng thất bại",
        ];
    }
}
