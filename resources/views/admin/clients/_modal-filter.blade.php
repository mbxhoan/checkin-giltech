<div class="modal fade" id="{{ $modalId }}" data-bs-keyboard="true" tabindex="-1"
    aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="{{ $modalId }}Label">
                    {{ $title }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ $route }}">
                <div class="modal-body text-sm">
                    <div class="row">
                        <div class="col-4">
                            @include('components.select', [
                                'label'         => __('imports.filters.field_date'),
                                'fieldName'     => 'field_date',
                                'id'            => 'field_date',
                                'options'       => $model->getDateFields(),
                                'selected'      => request()->has('field_date') ? request('field_date') : "",
                                'placeholder'   => null,
                            ])
                        </div>
                        <div class="col-4">
                            @include('components.form-groups.input-group', [
                                'id'                => "from_date",
                                'type'              => "date",
                                'value'             => request()->has('from_date') ? request('from_date') : "",
                                'label'             => __('imports.filters.from_date'),
                                'formClass'         => 'mb-3 col-12'
                            ])
                        </div>
                        <div class="col-4">
                            @include('components.form-groups.input-group', [
                                'id'                => "to_date",
                                'type'              => "date",
                                'value'             => request()->has('to_date') ? request('to_date') : "",
                                'label'             => __('imports.filters.to_date'),
                                'formClass'         => 'mb-3 col-12'
                            ])
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            @include('components.select', [
                                'label'         => __('imports.filters.status'),
                                'fieldName'     => 'status',
                                'id'            => 'status',
                                'options'       => ["" => "-"] + $model->getStatues(),
                                'selected'      => request()->has('status') ? request('status') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                        <div class="col-4">
                            @include('components.select', [
                                'label'         => __('imports.filters.type'),
                                'fieldName'     => 'type',
                                'id'            => 'type',
                                'options'       => ["" => "-"] + $model->getAvailableTypes($event->id),
                                'selected'      => request()->has('type') ? request('type') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                        <div class="col-4">
                            @include('components.select', [
                                'label'         => __('imports.filters.register_source'),
                                'fieldName'     => 'register_source',
                                'id'            => 'register_source',
                                'options'       => ["" => "-"] + $model->getAvailableRegisterSources(),
                                'selected'      => request()->has('register_source') ? request('register_source') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "checked_in_yes",
                            'fieldName'         => "checked_in",
                            'value'             => '1',
                            'checked'           => request('checked_in') == '1',
                            'type'              => "radio",
                            'label'             => "Đã checkin",
                            'showLabelTop'      => true,
                            'formClass'         => "col-4",
                            'inputClass'        => 'form-check-input',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "checked_in_no",
                            'fieldName'         => "checked_in",
                            'value'             => '0',
                            'checked'           => request('checked_in') == '0',
                            'type'              => "radio",
                            'label'             => "Chưa checkin",
                            'showLabelTop'      => true,
                            'formClass'         => "col-4",
                            'inputClass'        => 'form-check-input',
                        ])
                    </div>
                    {{-- ====== BỘ LỌC DYNAMIC TỪ custom_field_templates (tất cả kiểu multichoice) ====== --}}
                    @if(!empty($cfFilters))
                    <hr class="my-3">
                    <div class="row mb-2">
                        <div class="col-12">
                        <strong>Lọc theo trường thông tin</strong>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($cfFilters as $f)
                        <div class="col-4 mb-3">
                            @php
                            $fieldKey = $f['key'];                     
                            $selected = (array) request($fieldKey, []);
                            $label    = $f['label'] ?? $fieldKey;
                            @endphp

                            <label class="form-label">{{ $label }}</label>
                            @foreach($f['options'] as $k => $text)
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="{{ $fieldKey }}[]"
                                    id="{{ $fieldKey }}_{{ $k }}"
                                    value="{{ $k }}"
                                    @checked(in_array($k, $selected))>
                                <label class="form-check-label" for="{{ $fieldKey }}_{{ $k }}">{{ $text }}</label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ $route }}" class="btn btn-sm btn-danger">
                        @lang('imports.reset')
                    </a>

                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        @lang('common.close')
                    </button>

                    <button type="submit" class="btn btn-sm btn-primary">
                        {{ $submitBtn }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
