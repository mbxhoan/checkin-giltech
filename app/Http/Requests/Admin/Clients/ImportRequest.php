<?php

namespace App\Http\Requests\Admin\Clients;

use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Rules\UniqueCustomFieldByEventId;
use App\Services\Admin\ClientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportRequest extends FormRequest
{
    private $customFieldRules = [];
    private $customFieldTemplates;
    private $service;
    private $event;
    private $id = null;

    public function __construct(Event $event)
    {
        parent::__construct();
        $this->service = app(ClientService::class);
        $this->event = $event;
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
        $client = $this->service->findByAttributes([
            'qrcode' => $this->qrcode
        ]);

        $this->id = $client->id ?? null;
        $this->customFieldTemplates = CustomFieldTemplate::where('event_id', $this->event->id)->get();

        if (empty($this->qrcode)) {
            $this->merge([
                'qrcode' => $this->event->generateQrcodeOnSetting($this->event->code, $this->custom_fields['phone'] ?? null, $this->email ?? null, $this->name, $this->custom_fields)
            ]);
        }

        $rules = [
            'qrcode'            => [
                'nullable',
                // 'string',
                'regex:/^[a-zA-Z0-9\-+_#$*%]+$/',
                'max:200',
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
                'nullable',
                Rule::in(array_keys(Client::STATUES)), // Validate against allowed statuses
            ],
            'type'              => [
                'nullable',
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
                        $rules[$template->name][] = Rule::unique('clients')
                            ->ignore(optional($this->client)->id)
                            ->where(function ($query) {
                                return $query->where('event_id', $this->event->id);
                            });
                    }
                } else {
                    /* custom fields */
                    /* required */
                    $this->customFieldRules[$template->name] = [
                        $template->required ? 'required' : 'nullable',
                    ];

                    /* unique */
                    /* nạp file thì không thể ràng unique được? */
                    if ($template->unique) {
                        $this->customFieldRules[$template->name] = [
                            new UniqueCustomFieldByEventId($template->name, $this->event->id, $this->id),
                        ];
                    }

                    $options = [];

                    /* set simple array for options if existed */
                    if (!empty($template->options)) {
                        $options = $template->getOptionsAsArray();
                    }

                    switch ($template->type) {
                        case CustomFieldTemplate::TYPE_NUMBER:
                            $this->customFieldRules[$template->name][] = 'numeric';
                            break;
                        case CustomFieldTemplate::TYPE_CODE:
                            // $this->customFieldRules[$template->name][] = 'regex:/^[a-zA-Z0-9-+_#$*%]+$/';
                            $this->customFieldRules[$template->name][] = 'max:200';
                            break;
                        case CustomFieldTemplate::TYPE_TEL:
                            $this->customFieldRules[$template->name][] = 'regex:/^[0-9+\-\s_.()]{9,15}$/';
                            break;
                        case CustomFieldTemplate::TYPE_EMAIL:
                            $this->customFieldRules[$template->name][] = 'email';
                            $this->customFieldRules[$template->name][] = 'lowercase';
                            $this->customFieldRules[$template->name][] = 'max:255';
                            // regex for ASCII-only email (no Vietnamese diacritics)
                            $this->customFieldRules[$template->name][] = 'regex:/^[\x00-\x7F]+$/';
                            // Only ASCII before @
                            // $this->customFieldRules[$template->name][] = 'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/';
                            break;
                        case CustomFieldTemplate::TYPE_COLOR:
                            $this->customFieldRules[$template->name][] = 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/';
                            break;
                        case CustomFieldTemplate::TYPE_SELECT:
                            if (count($options)) {
                                $this->customFieldRules[$template->name][] = Rule::in(array_keys($options));
                            }

                            break;
                        case CustomFieldTemplate::TYPE_MULTICHOICE:
                            // $this->customFieldRules[$template->name][] = 'array';

                            if (count($options)) {
                                $this->customFieldRules["{$template->name}.*"][] = Rule::in(array_keys($options));
                            }

                            break;

                        default:
                            /* TEXT */
                            // $this->customFieldRules[$template->name][] = 'string';
                            $this->customFieldRules[$template->name][] = 'max:255';
                    }
                }
            }
        }

        return array_merge($rules, $this->customFieldRules);
    }

    public function attributes()
    {
        $attributes = [
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
                        $attributes[$template->name] = $template->description;
                    }
                }
            }
        }

        return $attributes;
    }
}
