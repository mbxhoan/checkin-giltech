<button type="button" class="{{ $class ?? 'btn btn-danger btn-sm' }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}Modal">
    {!! $icon ?? null !!}
    {{ $text ?? null }}
</button>

<!-- Modal Xác nhận Reset -->
<div class="modal fade" id="{{ $modalId }}Modal" tabindex="-1" aria-labelledby="{{ $modalId }}ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}ModalLabel">
                    Nhân bản sự kiện
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST" class="form-inline" data-confirm="{{ $confirm ?? __('forms.common.delete') }}">
                @csrf
                <div class="modal-body text-start">
                    <div class="row">
                        @sys_admin
                            <div class="mb-3 col-md-6">
                                @include('components.select', [
                                    'label'         => "Công ty",
                                    'fieldName'     => 'company_id',
                                    'id'            => 'company',
                                    'options'       => $companyArray,
                                    'selected'      => request()->company_id ?? $model->company_id,
                                    'placeholder'   => null,
                                    'required'      => true,
                                ])
                            </div>
                        @else
                            @include('components.form-groups.input-group', [
                                'id'                => "company_id",
                                'fieldName'         => "company_id",
                                'value'             => $company->id,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                        @endsys_admin
                    </div>
                    <div class="row">
                        {{-- @include('components.form-groups.input-group', [
                            'id'                => "code",
                            'model'             => null,
                            'value'             => null,
                            'type'              => "text",
                            'label'             => "Mã sự kiện",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                        ]) --}}
                        @include('components.form-groups.input-group', [
                            'id'                => "name",
                            'model'             => null,
                            'value'             => null,
                            'type'              => "text",
                            'label'             => "Tên sự kiện",
                            'placeholder'       => "Triển lãm xe hơi lần III - ".now()->format('Y'),
                            'required'          => true,
                            'formClass'         => 'mb-3 col-md-12',
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "from_date",
                            'model'             => $model,
                            'type'              => "date",
                            'value'             =>  $model->from_date ? $model->from_date->format('Y-m-d') : now()->format('Y-m-d'),
                            'label'             => "Ngày bắt đầu",
                            'formClass'         => 'mb-3 col-md-6'
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "to_date",
                            'model'             => $model,
                            'type'              => "date",
                            'value'             =>  $model->to_date ? $model->to_date->format('Y-m-d') : now()->format('Y-m-d'),
                            'label'             => "Ngày kết thúc",
                            'formClass'         => 'mb-3 col-md-6'
                        ])
                    </div>
                    <div class="row my-2">
                        <div class="col-md-12 text-xs">
                            <ul>
                                Các thông tin sẽ được nhân bản:
                                <li class="fst-italic mt-1">Thông tin sự kiện</li>
                                <li class="fst-italic mt-1">Cấu hình trường thông tin</li>
                                <li class="fst-italic mt-1">Cài đặt sự kiện</li>
                            </ul>
                        </div>
                    </div>
                    {{ $confirm }}
                    <div class="row my-2">
                        @include('components.form-groups.input-group', [
                            'id'                => "confirm",
                            'fieldName'         => "confirm",
                            'value'             => '',
                            'label'             => $label,
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-12',
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

