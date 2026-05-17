<div class="row">
    @foreach ($customFieldTemplates as $fieldName => $fieldAttr)
        @php
            $defaultOption = ["" => "-"];
            $options = $fieldAttr['options'] ?? [];
            $formClasses = $formClasses ?? "col-md-3";
        @endphp

        @switch($fieldAttr['type'])
            @case($cfTemplate::TYPE_SELECT)
                <div class="mb-3 {{ $formClasses }}">
                    @include('components.select', [
                        'id'            => "custom_fields.{$fieldName}",
                        'fieldName'     => "custom_fields[{$fieldName}]",
                        'options'       => $options,
                        'selected'      => $model->custom_fields[$fieldName] ?? null,
                        'label'         => $fieldAttr['desc'] ?? $fieldName,
                        'required'      => $fieldAttr['required'],
                        'unique'        => $fieldAttr['unique'],
                        'formClass'     => 'form-control custom-field',
                    ])
                </div>
                @break
            @case($cfTemplate::TYPE_SELECT2)
                <div class="mb-3 {{ $formClasses }}">
                    @include('components.select2', [
                        'id'            => "custom_fields.{$fieldName}",
                        'fieldName'     => "custom_fields[{$fieldName}]",
                        'options'       => $options,
                        'selected'      => $model->custom_fields[$fieldName] ?? null,
                        'label'         => $fieldAttr['desc'] ?? $fieldName,
                        'required'      => $fieldAttr['required'],
                        'unique'        => $fieldAttr['unique'],
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
                        'label'         => $fieldAttr['desc'] ?? $fieldName,
                        'required'      => $fieldAttr['required'],
                        'unique'        => $fieldAttr['unique'],
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
                        'label'         => $fieldAttr['desc'] ?? $fieldName,
                        'required'      => $fieldAttr['required'],
                        'unique'        => $fieldAttr['unique'],
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
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-check-input custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_SWITCH)
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "switch",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-check-input custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_NUMBER)
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "number",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-control custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_EMAIL)
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "email",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-control custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_TEL)
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "tel",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-control custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_COLOR)
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "color",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-control custom-field',
                ])
                @break
            @case($cfTemplate::TYPE_IMAGE)
                <div class="mb-3 {{ $formClasses }}">

                    @include('components.form-groups.input-group', [
                        'id'                => "custom_fields.{$fieldName}",
                        'fieldName'         => "custom_fields[{$fieldName}]",
                        'value'             => $model->custom_fields[$fieldName] ?? null,
                        'type'              => "text",
                        'label'             => $fieldAttr['desc'] ?? $fieldName,
                        'formClass'         => "",
                        'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                        'required'          => $fieldAttr['required'],
                        'unique'            => $fieldAttr['unique'],
                        'inputClass'        => 'form-control custom-field',
                    ])
                    @if (isset($model->custom_fields[$fieldName]))
                        <div class="w-100 mt-2">
                            <a href="{{ $model->custom_fields[$fieldName] ?? "#" }}" class="w-100" target="_blank">
                                <img src="{{ $model->custom_fields[$fieldName] ?? null }}" alt="{{ $model->custom_fields[$fieldName] ?? null }}" width="100">
                            </a>
                            <a href="{{ $model->custom_fields[$fieldName] ?? "#" }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>
                            <a href="{{ $model->custom_fields[$fieldName] ?? "#" }}"
                                title="@lang('media.download')"
                                class="btn btn-primary btn-sm"
                                download=""
                            >
                                <x-icon name="download" />
                            </a>
                        </div>
                    @endif
                </div>
                @break
            @default
                {{-- text code hidden --}}
                @include('components.form-groups.input-group', [
                    'id'                => "custom_fields.{$fieldName}",
                    'fieldName'         => "custom_fields[{$fieldName}]",
                    'value'             => $model->custom_fields[$fieldName] ?? null,
                    'type'              => "text",
                    'label'             => $fieldAttr['desc'] ?? $fieldName,
                    'formClass'         => "mb-3 {$formClasses}",
                    'placeholder'       => $fieldAttr['desc'] ?? $fieldName,
                    'required'          => $fieldAttr['required'],
                    'unique'            => $fieldAttr['unique'],
                    'inputClass'        => 'form-control custom-field',
                ])
        @endswitch
    @endforeach
</div>

@include('components.form-groups.input-group', [
    'id'                => "event_id",
    'model'             => $model,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])

@include('components.form-groups.input-group', [
    'id'                => "event_code",
    'model'             => $model,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
