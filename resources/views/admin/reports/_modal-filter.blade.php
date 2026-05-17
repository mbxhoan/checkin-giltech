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
                        <div class="col-md-4 col-12">
                            @include('components.select', [
                                'label'         => "Trường",
                                'fieldName'     => 'field_date',
                                'id'            => 'field_date',
                                'options'       => $model->getDateFields(),
                                'selected'      => request()->has('field_date') ? request('field_date') : "",
                                'placeholder'   => null,
                            ])
                        </div>
                        <div class="col-md-4 col-12">
                            @include('components.form-groups.input-group', [
                                'id'                => "from_date",
                                'type'              => "date",
                                'value'             => request()->has('from_date') ? request('from_date') : "",
                                'label'             => "Từ ngày",
                                'formClass'         => 'mb-3 col-12'
                            ])
                        </div>
                        <div class="col-md-4 col-12">
                            @include('components.form-groups.input-group', [
                                'id'                => "to_date",
                                'type'              => "date",
                                'value'             => request()->has('to_date') ? request('to_date') : "",
                                'label'             => "Đến ngày",
                                'formClass'         => 'mb-3 col-12'
                            ])
                        </div>
                    </div>
                    <div class="row">
                        @sys_admin
                            @if (isset($companyArray))
                                <div class="col-md-4 col-12">
                                    @include('components.select', [
                                        'label'         => "Công ty",
                                        'fieldName'     => 'company_id',
                                        'id'            => 'company_id',
                                        'options'       => ["" => "-"] + $companyArray,
                                        'selected'      => request()->has('company_id') ? request('company_id') : null,
                                        'placeholder'   => null,
                                    ])
                                </div>
                            @endif
                        @endsys_admin
                        @if (isset($proviceArray))
                            <div class="col-md-4 col-12">
                                @include('components.select', [
                                    'label'         => "Tỉnh/Thành phố",
                                    'fieldName'     => 'province_id',
                                    'id'            => 'province_id',
                                    'options'       => ["" => "-"] + $proviceArray,
                                    'selected'      => request()->has('province_id') ? request('province_id') : null,
                                    'placeholder'   => null,
                                ])
                            </div>
                        @endif
                        <div class="col-md-4 col-12">
                            @include('components.select', [
                                'label'         => "Trạng thái",
                                'fieldName'     => 'status',
                                'id'            => 'status',
                                'options'       => ["" => "-"] + $model->getStatues(),
                                'selected'      => request()->has('status') ? request('status') : null,
                                'placeholder'   => null,
                            ])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ $route }}" class="btn btn-sm btn-danger">
                        Reset
                    </a>

                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>

                    <button type="submit" class="btn btn-sm btn-primary">
                        {{ $submitBtn }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
