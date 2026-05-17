<div class="data-form">
    <form id="event-form" name="event-form" action="{{ $urlSubmitForm }}" method="POST">
        @csrf
        @foreach ($customFieldTemplates as $fieldName => $fieldAttr)
            <div class="row">
                @php
                    $defaultOption = ["" => "-"];
                    $options = $fieldAttr['options'] ?? [];
                    $formClasses = $formClasses ?? "col-12";

                    if (count($options)) {
                        foreach ($options as $key => $value) {
                            $options[$key] = lang_trans("{$event->code}.fields.{$fieldName}_{$key}", "lp", $value);
                        }
                    }
                @endphp
                @switch($fieldAttr['type'])
                    @case($cfTemplate::TYPE_SELECT)
                        <div class="mb-3 {{ $formClasses }}">
                            @include('components.select', [
                                'id'            => "custom_fields.{$fieldName}",
                                'fieldName'     => "custom_fields[{$fieldName}]",
                                'options'       => $options,
                                'selected'      => $model->custom_fields[$fieldName] ?? null,
                                'label'         => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                                'required'      => $fieldAttr['required'],
                                // 'unique'        => $fieldAttr['unique'],
                                'formClass'     => 'form-control custom-field',
                            ])
                        </div>
                        @break
                    @case($cfTemplate::TYPE_MULTICHOICE)
                        <div class="mb-3 {{ $formClasses }}">
                            @include('components.multichoice', [
                                'id'            => "custom_fields.{$fieldName}",
                                'fieldName'     => "custom_fields[{$fieldName}]",
                                'options'       => $options,
                                'values'        => $model->custom_fields[$fieldName] ?? [],
                                'label'         => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                                'required'      => $fieldAttr['required'],
                                // 'unique'        => $fieldAttr['unique'],
                            ])
                        </div>
                        @break
                    @case($cfTemplate::TYPE_RADIO)
                        <div class="mb-3 {{ $formClasses }}">
                            @include('components.radio', [
                                'id'            => "custom_fields.{$fieldName}",
                                'fieldName'     => "custom_fields[{$fieldName}]",
                                'options'       => $options,
                                'selected'      => $model->custom_fields[$fieldName] ?? null,
                                'label'         => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                                'required'      => $fieldAttr['required'],
                                // 'unique'        => $fieldAttr['unique'],
                            ])
                        </div>
                        @break
                    @case($cfTemplate::TYPE_CHECKBOX)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'checked'           => $model->custom_fields[$fieldName] ?? 0,
                            'type'              => "checkbox",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'showLabelTop'      => false,
                            'formClass'         => "mb-3 {$formClasses}",
                            'inputClass'        => 'aaaa',
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-check-input custom-field',
                        ])
                        @break
                    @case($cfTemplate::TYPE_SWITCH)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "switch",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-check-input custom-field',
                        ])
                        @break
                    @case($cfTemplate::TYPE_NUMBER)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "number",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-control custom-field',
                        ])
                        @break
                    @case($cfTemplate::TYPE_EMAIL)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => $fieldAttr['is_default'] ? $fieldName : "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "email",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-control custom-field',
                        ])
                        @break
                    @case($cfTemplate::TYPE_TEL)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "tel",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-control custom-field',
                        ])
                        @break
                    @case($cfTemplate::TYPE_COLOR)
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "color",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-control custom-field',
                        ])
                        @break
                    @default
                        {{-- text code hidden --}}
                        @include('components.form-groups.input-group', [
                            'id'                => "custom_fields.{$fieldName}",
                            'fieldName'         => $fieldAttr['is_default'] ? $fieldName : "custom_fields[{$fieldName}]",
                            'value'             => $model->custom_fields[$fieldName] ?? null,
                            'type'              => "text",
                            'label'             => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'formClass'         => "mb-3 {$formClasses}",
                            'placeholder'       => lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName),
                            'required'          => $fieldAttr['required'],
                            // 'unique'            => $fieldAttr['unique'],
                            'inputClass'        => 'form-control custom-field',
                        ])
                @endswitch
            </div>
        @endforeach
        @if ($openCaptcha)
            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'                => "g-recaptcha-response",
                    'type'              => "recaptcha",
                    'formClass'         => 'text-center',
                ])
            </div>
        @endif
        <div class="row pt-4">
            <div class="col-12 text-center">
                @include('web.landing_pages.components.submit', [
                    'btnId'         => 'btn_submit-text',
                    'btnText'       => lang_trans("{$event->code}.btn_submit", "lp", "Đăng ký"),
                    'btnClass'      => 'btn btn-primary',
                    'id'            => 'btn_submit',
                ])
            </div>
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "event_id",
            'fieldName'         => "event_id",
            'model'             => $model,
            'value'             => $model->event_id,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "campaign_id",
            'fieldName'         => "campaign_id",
            'value'             => $campaign->id ?? null,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "lang",
            'fieldName'         => "lang",
            'value'             => request()->lang ?? app()->getLocale(),
            'model'             => null,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
    </form>
</div>
