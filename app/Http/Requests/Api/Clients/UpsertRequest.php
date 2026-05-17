<?php

namespace App\Http\Requests\Api\Clients;

use App\Http\Requests\BaseFormRequest;
use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Rules\UniqueCustomFieldByAttributes;
use Illuminate\Validation\Rule;

class UpsertRequest extends BaseFormRequest
{
    private $customFieldRules = [];
    private $customFieldTemplates;

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
        $allowSize = config('app.upload_media_size_max');
        $eventCode = $this->input('event_code');
        $this->customFieldTemplates = CustomFieldTemplate::whereHas('event', function ($query) use ($eventCode) {
            $query->where('code', $eventCode);
        })->get();

        $rules = [
            // 'id'                => 'nullable|integer',
            'event_id'          => 'required|integer|exists:events,id',
            'qrcode'            => [
                'nullable',
                // Rule::unique('clients')->where(function ($query) {
                //     return $query->where('event_id', $this->event_id);
                // })->ignore(optional($this->client)->id),
                // 'unique:clients,qrcode,NULL,id,event_id,' . $this->event_id,
                'regex:/^[a-zA-Z0-9-+_#$*%]+$/'
            ],
            'name'              => [
                'string',
                'max:255'
            ],
            'email'             => [
                'email',
                'max:255',
                // 'lowercase'
            ],
            'status'            => [
                'nullable',
                Rule::in(array_keys(Client::STATUES)), // Validate against allowed statuses
            ],
            'type'              => [
                'nullable',
                'string',
                'max:50'
            ],
            'campaign_id'       => [
                'nullable',
                'integer',
                'exists:campaigns,id'
            ],
            'ref_id'            => [
                'nullable',
                'integer',
            ]
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
                            new UniqueCustomFieldByAttributes($template->name, [
                                'event_code' => $eventCode,
                            ], $this->id ?? null),
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
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^[a-zA-Z0-9-+_#$*%]+$/';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:200';
                            break;
                        case CustomFieldTemplate::TYPE_TEL:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^[0-9+\-\s_.()]{9,15}$/';
                            break;
                        case CustomFieldTemplate::TYPE_EMAIL:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'email';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:255';
                            break;
                        case CustomFieldTemplate::TYPE_COLOR:
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/';
                            break;
                        case CustomFieldTemplate::TYPE_FILE:
                            if ($accepts = json_decode($template->accepts ?? '', true)) {
                                $accepts = implode(',', array_map(fn($ext) => ltrim($ext, '.'), $accepts));
                                // $accepts = implode(',', $accepts);
                                $this->customFieldRules["custom_fields.{$template->name}.*"][] = "file";
                                $this->customFieldRules["custom_fields.{$template->name}.*"][] = "mimes:{$accepts}";
                                $this->customFieldRules["custom_fields.{$template->name}.*"][] = "max:{$allowSize}";
                                // $this->customFieldRules["custom_fields.{$template->name}"][] = "mimes:{$accepts}|max:{$allowSize}";
                            }
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
        $attributes = [
            'campaign_id'   => "Campaign",
            'event_id'      => "Sự kiện",
            'event_code'    => "Mã sự kiện",
            'name'          => "Họ tên",
            'email'         => "Email",
            'phone'         => "Số điện thoại",
            'status'        => "Trạng thái",
        ];

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
}
