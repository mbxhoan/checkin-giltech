<?php

namespace App\Http\Requests\Admin;

use App\Models\Audio;
use App\Services\Admin\AudioService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AudiosRequest extends FormRequest
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = app(AudioService::class);
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
            'text'      => 'required|string|max:100',
            'voice'     => [
                'required',
                'string',
                'max:50',
                Rule::in(array_values(Audio::getAITTtsModel())),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'text'                  => 'Chuỗi',
            'voice'                 => 'Giọng nói',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         if (!$this->service->ensureLimited($this->input('company_id'), 'limited_events')) {
    //             $validator->errors()->add('company_id', 'Đã vượt quá số lượng sự kiện cho phép');
    //         }
    //     });
    // }
}
