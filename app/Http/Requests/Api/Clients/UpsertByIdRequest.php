<?php

namespace App\Http\Requests\Api\Clients;

use App\Http\Requests\BaseFormRequest;
use App\Models\Event;
use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Rules\UniqueCustomFieldByAttributes;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class UpsertByIdRequest extends BaseFormRequest
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

    protected function prepareForValidation(): void
    {
        $data = [];

        // Normalize id nếu frontend gửi "", "null" thay vì null thật
        if ($this->has('id')) {
            $id = $this->input('id');

            $data['id'] = in_array($id, ['', 'null', 'NULL'], true) ? null : $id;
        }

        if ($this->filled('email') && is_string($this->input('email'))) {
            $data['email'] = mb_strtolower(trim($this->input('email')));
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->filled('email') || !$this->filled('event_id')) {
                return;
            }

            $email = mb_strtolower(trim((string) $this->input('email')));
            $ignoreClientId = $this->filled('id') ? (int) $this->input('id') : null;

            $query = Client::query()
                ->where('event_id', (int) $this->input('event_id'))
                ->whereRaw('LOWER(email) = ?', [$email]);

            // Update: bỏ qua chính client đang update
            if ($ignoreClientId) {
                $query->where('id', '!=', $ignoreClientId);
            }

            if ($query->exists()) {
                $validator->errors()->add('email', 'Email đã có người dùng');
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $eventCode = $this->input('event_code');
        if (!$eventCode && $this->filled('event_id')) {
            $eventCode = Event::query()->whereKey($this->input('event_id'))->value('code');
        }

        $this->customFieldTemplates = CustomFieldTemplate::whereHas('event', function ($query) use ($eventCode) {
            $query->where('code', $eventCode);
        })->get();

        $rules = [
            'id'                => 'nullable|integer|exists:clients,id',
            'event_id'          => 'required|integer|exists:events,id',
            'name'              => [
                'string',
                'max:255'
            ],
            'email'             => [
                'nullable',
                'email',
                'max:255',
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

        $rules['custom_fields'] = ['nullable', 'array'];
        $rules['items'] = ['nullable', 'array'];
        $rules['items.*.ticket_id'] = ['required_with:items', 'integer', 'exists:tickets,id'];
        $rules['items.*.quantity'] = ['nullable', 'integer', 'min:1'];

        if ($this->customFieldTemplates) {
            foreach ($this->customFieldTemplates as $template) {
                if ($template->is_default && $template->name != 'qrcode') {
                    /* default fields except qrcode */
                    $rules[$template->name][] = $template->required ? 'required' : 'nullable';

                    if ($template->unique) {
                        // $rules[$template->name][] = "unique:clients,{$template->name},".(optional($this->client)->id ?: 'NULL');
                        /* ràng unique thêm event_id */
                        $uniqueRule = Rule::unique('clients', $template->name)
                            ->where(function ($query) {
                                return $query->where('event_id', $this->input('event_id'));
                            });

                        if ($this->filled('id')) {
                            $uniqueRule->ignore((int) $this->input('id'));
                        }

                        $rules[$template->name][] = $uniqueRule;
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
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'string';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'max:40';
                            $this->customFieldRules["custom_fields.{$template->name}"][] = 'regex:/^rf_[A-Z0-9]{26}$/';
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
