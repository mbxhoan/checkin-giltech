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
                        @include('components.form-groups.input-group', [
                            'id'                => "unique_qrcode",
                            'fieldName'         => "unique_qrcode",
                            'value'             => 1,
                            'checked'           => request('unique_qrcode') == '1',
                            'type'              => "checkbox",
                            'label'             => "Checkin 1 lần duy nhất",
                            'showLabelTop'      => true,
                            'formClass'         => "col-12",
                            'inputClass'        => 'form-check-input',
                        ])
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
                                'options'       => ["" => "-"] + $model->getTypes(),
                                'selected'      => request()->has('type') ? request('type') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                        <div class="col-4">
                            @include('components.select', [
                                'label'         => "Nhóm",
                                'fieldName'     => 'scan_type',
                                'id'            => 'scan_type',
                                'options'       => ["" => "-"] + ($scan_types ?? []),
                                'selected'      => request()->has('scan_type') ? request('scan_type') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                    </div>
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
