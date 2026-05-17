<?php

namespace App\Http\Requests\Admin\Events;

use App\Models\Event;
use App\Services\Admin\EventService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventsRequest extends FormRequest
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = app(EventService::class);
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
        $allowTypes = config('app.upload_media_allow_types');
        $allowSize = config('app.upload_media_size_max');

        return [
            // 'code'          => [
            //     'nullable',
            //     'regex:/^[a-zA-Z0-9\-+_.!@*]+$/',
            //     'unique:events,code,' . (optional($this->event)->id ?: 'NULL'),
            // ],
            'company_id'    => 'required|integer|exists:companys,id',
            'province_id'   => 'nullable|integer|exists:provinces,id',
            'name'          => 'required|string|max:255',
            'from_date'     => [
                'nullable',
                'date'
            ],
            'to_date'       => [
                'nullable',
                'date',
                'after_or_equal:from_date'
            ],
            'status'        => [
                'required',
                Rule::in(array_keys(Event::STATUES)),
            ],
            'logo'          => "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'features'      => [
                'nullable',
                'array',
                'max:50',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'code' => 'Mã sự kiện',
            'name' => 'Tên sự kiện',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('company_id')) {
                if (!$this->service->ensureLimited($this->input('company_id'), 'limited_events')) {
                    $validator->errors()->add('company_id', 'Đã vượt quá số lượng sự kiện cho phép');
                }
            }
        });
    }
}
