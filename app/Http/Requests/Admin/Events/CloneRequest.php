<?php

namespace App\Http\Requests\Admin\Events;

use App\Services\Admin\EventService;
use Illuminate\Foundation\Http\FormRequest;

class CloneRequest extends FormRequest
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
        return [
            'company_id'    => 'required|integer|exists:companys,id',
            // 'code'          => [
            //     'required',
            //     'regex:/^[a-zA-Z0-9\-+_.!@*]+$/',
            //     'unique:events,code,' . (optional($this->event)->id ?: 'NULL'),
            // ],
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
