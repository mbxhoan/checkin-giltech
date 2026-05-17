<div>
    <div class="row">
        <div class="col-md-3 fw-bold text-sm">
            Trường
        </div>
        <div class="col-md-3 fw-bold text-sm">
            Mô tả
        </div>
        <div class="col-md-2 fw-bold text-sm">

        </div>
        <div class="col-md-3 fw-bold text-sm">
            Hiển thị
        </div>
        <div class="col-md-1"></div>
    </div>
    <div id="sortable-wrapper">
        @foreach ($customFieldTemplates as $customFieldTemplate)
            @php
                $order = $customFieldTemplate->order;
            @endphp
            <div id="" class="sortable-item" data-id="{{ $customFieldTemplate->id }}">
                <form action="{{ route('admin.custom_field_templates.update', [
                        'custom_field_template' => $customFieldTemplate
                    ]) }}"
                    id="custom-field-template-{{ $customFieldTemplate->id }}"
                    class="mb-2 pb-2 bg-light rounded shadow-sm {{ $customFieldTemplate->is_default ? "bg-light" : "" }}"
                    method="POST"
                >
                    @method('PUT')
                    @csrf
                    <div class="row pt-2 px-2">
                        @include('components.form-groups.input-group', [
                            'id'                => "custom-field-template-{$customFieldTemplate->id}",
                            'fieldName'         => "event_id",
                            'value'             => $event->id,
                            'type'              => "hidden",
                            'formClass'         => 'd-none',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "custom-field-template-{$customFieldTemplate->id}",
                            'fieldName'         => "order[]",
                            'value'             => $order,
                            'type'              => "hidden",
                            'formClass'         => 'd-none',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "custom-field-template-{$customFieldTemplate->id}",
                            'fieldName'         => "name",
                            'value'             => $customFieldTemplate->name,
                            'type'              => "text",
                            'formClass'         => 'mb-2 col-md-3',
                            'inputClass'        => "text-sm edit-change-field w-100",
                            'disabled'          => $customFieldTemplate->is_default ? true : false,
                            'placeholder'       => "Tên",
                            'errorPop'          => false,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "custom-field-template-{$customFieldTemplate->id}",
                            'fieldName'         => "description",
                            'value'             => $customFieldTemplate->description,
                            'type'              => "text",
                            'formClass'         => 'mb-2 col-md-3',
                            'inputClass'        => "text-sm edit-change-field w-100",
                            'placeholder'       => "Mô tả",
                            'errorPop'          => false,
                        ])
                        <div class="col-md-2">
                            @include('components.select', [
                                'fieldName'     => 'type',
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'options'       => $customFieldTemplate->getTypes(),
                                'selected'      => $customFieldTemplate->type,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm edit-change-field w-100',
                                'disabled'      => $customFieldTemplate->is_default ? true : false,
                            ])
                        </div>
                        <div class="col-md-3">
                            @if ($languageCode)
                                @include('components.form-groups.input-group', [
                                    // 'id'                => $languageCode,
                                    'id'                => "fields.{$customFieldTemplate->name}",
                                    // 'fieldName'         => "fields.{$customFieldTemplate->name}",
                                    'value'             => $model->getTranslate("fields.{$customFieldTemplate->name}", $languageCode)->translate ?? null,
                                    'type'              => "text",
                                    'formClass'         => 'mb-2',
                                    'inputClass'        => "text-sm w-100 edit-translate-field",
                                    'placeholder'       => $customFieldTemplate->description,
                                    'changeUrl'         => route('admin.language_defines.edit-value'),
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "event_id",
                                    'value'             => $event->id,
                                    'type'              => "hidden",
                                    'formClass'         => "d-none",
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "language_id",
                                    'value'             => $language->id,
                                    'type'              => "hidden",
                                    'formClass'         => "d-none",
                                ])
                            @endif
                        </div>
                        <div class="col-md-1 px-1">
                            @if (!$customFieldTemplate->is_default)
                                <a href="" id="{{ $customFieldTemplate->id }}"
                                    class="text-xs text-danger btn-del-template w-100"
                                    data-id="custom-field-template-{{ $customFieldTemplate->id }}"
                                    data-url="{{ route('admin.custom_field_templates.destroy', [
                                        'custom_field_template' => $customFieldTemplate
                                    ]) }}"
                                >
                                    <x-icon name="trash" />
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <input type="hidden" name="required" value="0">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "required",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => "Bắt buộc",
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => $customFieldTemplate->required,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                            'disabled'      => in_array($customFieldTemplate->name, ['qrcode', 'name']) ? true : false
                        ])
                        <input type="hidden" name="unique" value="0">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "unique",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => "Duy nhất",
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => in_array($customFieldTemplate->name, ['qrcode']) ? true : $customFieldTemplate->unique,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                            'disabled'      => in_array($customFieldTemplate->name, ['qrcode']) ? true : false
                        ])
                        <input type="hidden" name="is_lp" value="0">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "is_lp",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => "Hiển thị",
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => $customFieldTemplate->is_lp,
                            'formClass'     => 'mb-0 col-md-4',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                            'disabled'      => $customFieldTemplate->name == "name" ? true : false,
                        ])
                        @if (in_array($customFieldTemplate->type, [
                            $customFieldTemplate::TYPE_FILE
                        ]))
                            <div class="col-md-2">
                                @include('admin.custom_field_templates._validate-type-file', [
                                    'customFieldTemplate' => $customFieldTemplate
                                ])
                            </div>
                        @endif
                    </div>
                    @if (in_array($customFieldTemplate->type, $customFieldTemplate::TYPE_USE_OPTIONS))
                        @include('admin.landing_pages.custom_field_templates._row-options', [
                            'customFieldTemplate'   => $customFieldTemplate,
                            'languageCode'          => $languageCode,
                        ])
                    @endif
                </form>
            </div>
        @endforeach
    </div>
    <form
        {{-- wire:submit.prevent="createTemplate" --}}
        action="{{ route('admin.custom_field_templates.store') }}"
        id="empty-row"
        class="px-2"
        method="POST"
    >
        @csrf
        @include('components.form-groups.input-group', [
            'id'                => "new.order",
            'fieldName'         => "new[order]",
            'value'             => ++$order,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        <div class="row mt-2">
            @include('components.form-groups.input-group', [
                'id'                => "new.name",
                'fieldName'         => "new[name]",
                'value'             => "",
                'type'              => "text",
                'formClass'         => 'mb-2 col-md-3',
                'inputClass'        => "text-sm w-100",
                'placeholder'       => "tên",
            ])
            @include('components.form-groups.input-group', [
                'id'                => "new.description",
                'fieldName'         => "new[description]",
                'value'             => "",
                'type'              => "text",
                'formClass'         => 'mb-2 col-md-3',
                'inputClass'        => "text-sm w-100",
                'placeholder'       => "mô tả",
            ])
            <div class="col-md-2">
                @include('components.select', [
                    'fieldName'     => 'new[type]',
                    'id'            => "new.type",
                    'options'       => $customFieldTemplate->getTypes(),
                    'selected'      => $customFieldTemplate::TYPE_TEXT,
                    'placeholder'   => null,
                    'formClass'     => 'text-sm w-100',
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "new[event_id]",
                'value'             => $event->id,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])

            <div class="col-md-2">
                <button type="submit" class="btn btn-xs btn-primary">
                    <x-icon name="save" />
                </button>
            </div>
        </div>
    </form>
</div>

