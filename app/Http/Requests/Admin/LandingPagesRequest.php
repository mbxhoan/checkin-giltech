<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Models\LandingPage;
use App\Services\Admin\LandingPageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPagesRequest extends FormRequest
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = app(LandingPageService::class);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->isSysAdmin()) {
            return true;
        }

        return $user->authorizeSelfByEventId($this->event_id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $allowTypes = config('app.upload_media_allow_types');
        $allowSize = config('app.upload_media_size_max');

        $rules = [
            'event_id'      => 'nullable|integer|exists:events,id',
            'template_id'   => [
                'required',
                'integer',
                Rule::in(array_keys(LandingPage::getTemplates())),
            ],
            'slug'          => [
                'required',
                'regex:/^[a-zA-Z0-9-_]+$/', // only lowercase, numbers, dashes, underscores
                'unique:landing_pages,slug,' . (optional($this->landing_page)->id ?: 'NULL'),
                'max:50'
            ],
            'align'                 => [
                'nullable',
                Rule::in(array_keys(Event::ALIGNS)),
            ],
            'form_width'            => [
                'nullable',
                'numeric',
            ],
            'banner_id'             =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'header_id'             =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'footer_id'             =>  "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'status'                => [
                'required',
                'max:50',
                Rule::in(array_keys(LandingPage::STATUES)), // Validate against allowed statuses
            ],
            'contact_name'          => [
                'nullable',
                'string',
                'max:255',
            ],
            'contact_phone'          => [
                'nullable',
                'regex:/^[0-9+\-\s_.()]{9,15}$/',
            ],
            'contact_email'          => [
                'nullable',
                'email',
                'lowercase',
                'max:255',
            ],
            'contact_address'        => [
                'nullable',
                'string',
                'max:255',
            ],
            'languages'              => [
                'nullable',
                'array',
            ],
            'campaign_ids'           => [
                'nullable',
                'array',
            ],
            'campaign_ids.*.*'        => [
                'integer',
                'exists:campaigns,id',
            ],
            'card_ids'              => [
                'nullable',
                'array',
            ],
            'card_ids.*.*'            => [
                'integer',
                'exists:cards,id',
            ],
        ];

        if (!auth()->user()->isSysAdmin()) {
            if (empty(auth()->user()->company->languages)) {
                $rules['languages'] = [
                    'required',
                    'array',
                ];
            }
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'align'                 => 'Canh form',
            'template_id'           => 'Template page',
            'form_width'            => 'Chiều rộng form',
            'banner_id'             => 'Banner',
            'header_id'             => 'Header',
            'footer_id'             => 'Footer',
            'bg_desktop_id'         => 'Background PC',
            'bg_tablet_id'          => 'Background Tablet',
            'languages'             => "Ngôn ngữ",
            'languages'             => "Ngôn ngữ",
            'contact_name'          => "Họ tên",
            'contact_phone'         => "Số điện thoại",
            'contact_email'         => "Email",
            'contact_address'       => "Địa chỉ",
            'campaign_ids'          => "Campign gửi mail",
            'card_ids'              => "Thiệp/Thư mời",
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->service->ensureLimited($this->input('event_id'))) {
                $validator->errors()->add('event_id', 'Đã vượt quá số lượng trang cho phép');
            }
        });
    }
}
