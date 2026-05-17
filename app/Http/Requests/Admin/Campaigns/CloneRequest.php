<?php

namespace App\Http\Requests\Admin\Campaigns;

use App\Services\Admin\CampaignService;
use Illuminate\Foundation\Http\FormRequest;

class CloneRequest extends FormRequest
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = app(CampaignService::class);
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

        ];
    }

    public function attributes()
    {
        return [];
    }

    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     if ($this->filled('company_id')) {
        //         if (!$this->service->ensureLimited($this->input('company_id'), 'limited_events')) {
        //             $validator->errors()->add('company_id', 'Đã vượt quá số lượng sự kiện cho phép');
        //         }
        //     }
        // });
    }
}
