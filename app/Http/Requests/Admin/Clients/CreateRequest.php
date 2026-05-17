<?php

namespace App\Http\Requests\Admin\Clients;

use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Rules\UniqueCustomFieldByEventId;
use App\Services\Middleware\ClientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    private $customFieldRules = [];
    private $customFieldTemplates;
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = app(ClientService::class);
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
        $this->customFieldTemplates = CustomFieldTemplate::where('event_id', $this->event_id)->get();
        $allowTypes = config('app.upload_media_allow_types');
        $allowSize = config('app.upload_media_size_max');

        $rules = [
            'avatar'            => "nullable|file|mimes:{$allowTypes}|max:{$allowSize}",
            'event_id'          => 'required|integer|exists:events,id',
            'event_code'        => 'required|string|exists:events,code',
            'qrcode'            => [
                'required',
                // 'unique:clients,qrcode,' . (optional($this->client)->id ?: 'NULL'),
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('event_id', $this->event_id);
                })->ignore(optional($this->client)->id),
                'unique:clients,qrcode,NULL,id,event_id,' . $this->event_id,
                // 'regex:/^[a-zA-Z0-9-+_#$*%]+$/'
            ],
            'name'              => [
                'string',
                'max:255'
            ],
            'email'             => [
                'email',
                'max:255',
                'regex:/^[\x00-\x7F]+$/',
            ],
            'status'            => [
                'required',
                Rule::in(array_keys(Client::STATUES)), // Validate against allowed statuses
            ],
            'type'              => [
                'nullable',
                'string',
                'max:50'
            ],
            'register_soure'    => [
                'nullable',
                'string',
                'max:50'
            ],
        ];

        if ($this->customFieldTemplates) {
            foreach ($this->customFieldTemplates as $template) {
                if ($template->is_default && $template->name != 'qrcode') {
                    /* default fields except qrcode */
                    $rules[$template->name][] = $template->required ? 'required' : 'nullable';

                    if ($template->unique) {
                        // $rules[$template->name][] = "unique:clients,{$template->name},".(optional($this->client)->id ?: 'NULL');
                        /* ràng unique thêm event_id */
                        $rules[$template->name][] = Rule::unique('clients')->where(function ($query) {
                            return $query->where('event_id', $this->input('event_id'));
                        })->ignore(optional($this->client)->id);
                    }
                } else {
                    /* custom fields */
                    /* required */
                    $this->customFieldRules["custom_fields.{$template->name}"] = [
                        $template->required ? 'required' : 'nullable',
                    ];

                    /* unique */
                    if ($template->unique) {
                        $this->customFieldRules["custom_fields.{$template->name}"] = [
                            new UniqueCustomFieldByEventId($template->name, $this->event_id, $this->id ?? null),
                        ];
                    }

                    $options = [];

                    /* set simple array for options if existed */
                    if (!empty($template->options)) {
                        $options = $template->getOptionsAsArray();
                    }

                    switch ($template->type) {
                        case CustomFieldTemplate::TYPE_NUMBER:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'numeric';
                            break;
                        case CustomFieldTemplate::TYPE_CODE:
                            // $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^[a-zA-Z0-9-+_#$*%]+$/';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:200';
                            break;
                        case CustomFieldTemplate::TYPE_TEL:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^[0-9+\-\s_.()]{9,15}$/';
                            break;
                        case CustomFieldTemplate::TYPE_EMAIL:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'email';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'lowercase';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:255';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^[\x00-\x7F]+$/';
                            break;
                        case CustomFieldTemplate::TYPE_COLOR:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/';
                            break;
                        case CustomFieldTemplate::TYPE_SELECT:
                            if (count($options)) {
                                $this->customFieldRules["custom_fields.{$template->name}"][] = Rule::in(array_keys($options));
                            }

                            break;
                        case CustomFieldTemplate::TYPE_MULTICHOICE:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'array';

                            if (count($options)) {
                                $this->customFieldRules["custom_fields.{$template->name}.*"][] = Rule::in(array_keys($options));
                            }

                            break;

                        default:
                            /* TEXT */
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'string';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:255';
                    }
                }
            }
        }

        return array_merge($rules, $this->customFieldRules);
    }

    public function attributes()
    {
        $attributes = [];

        if ($this->customFieldTemplates) {
            foreach ($this->customFieldTemplates as $template) {
                if ($template->is_default) {
                    /* default fields */
                    if (isset($attributes[$template->name]) && !empty($template->description)) {
                        $attributes[$template->name] = $template->description;
                    }
                } else {
                    /* custom fields */
                    if (!empty($template->description)) {
                        $attributes["custom_fields.{$template->name}"] = $template->description;
                    }
                }
            }
        }

        return $attributes;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('event_id')) {
                if (!$this->service->ensureLimited($this->input('event_id'), 'limited_clients')) {
                    $validator->errors()->add('event_id', 'Đã vượt quá số lượng dữ liệu cho phép');
                }
            }
        });
    }
}
