<div class="row">
    @if (!empty($company))
        @include('components.form-groups.input-group', [
            'id'                => "company_id",
            'fieldName'         => "company_id",
            'value'             => $company->id,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
    @else
        @sys_admin
            <div class="mb-3 col-md-3">
                @include('components.select', [
                    'label'         => $model->company_id ?
                        '<a href="'.route('admin.companys.edit', $model->company).'" target="_blank">Công ty <i class="fa-solid fa-edit fa-xs"></i></a>' :
                        "Công ty",
                    'fieldName'     => 'company_id',
                    'id'            => 'company_id',
                    'options'       => $companyArray,
                    'selected'      => request()->company_id ?? $model->company_id,
                    'placeholder'   => null,
                    'required'      => true,
                ])
            </div>
        @endsys_admin
    @endif
    @sys_admin
       @include('components.form-groups.input-group', [
            'id'                => "code",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Mã sự kiện",
            'formClass'         => 'mb-3 col-md-3',
            'required'          => true,
            // 'readonly'          => !$model->isNew() ? true : false,
        ])
    @else
        @if (!$model->isNew())
            @include('components.form-groups.input-group', [
                'id'                => "code",
                'model'             => $model,
                'type'              => "text",
                'label'             => "Mã sự kiện",
                'formClass'         => 'mb-3 col-md-3',
                'required'          => true,
                'readonly'          => !$model->isNew() ? true : false,
            ])
        @endif
    @endsys_admin
    @include('components.form-groups.input-group', [
        'id'                => "name",
        'model'             => $model,
        'type'              => "text",
        'label'             => "Tên sự kiện",
        'placeholder'       => "Hội thảo răng hàm mặt ".now()->format('Y'),
        'required'          => true,
        'formClass'         => 'mb-3 col-md-4',
    ])
    <div class="mb-3 col-md-2">
        @include('components.select', [
            'label'         => "Trạng thái",
            'id'            => 'status',
            'fieldName'     => 'status',
            'options'       => $model->getStatues(),
            'selected'      => $model->status,
            'placeholder'   => null,
        ])
    </div>
    <div class="mb-3 col-md-2">
        @if ($model->isNew())
            <div class="form-group">
                <label for="" class="form-label"></label>
                <div class="mt-2">
                    <a href="" class="btn btn-sm btn-primary w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#selectFeatureEventModal"
                    >
                        <x-icon name="sliders"/>
                        Thiết lập tính năng
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="row">
    <div class="mb-3 col-md-3">
        @include('components.select', [
            'label'         => "Tỉnh/Thành phố",
            'fieldName'     => 'province_id',
            'id'            => 'province_id',
            'options'       => $proviceArray,
            'selected'      => $model->province_id,
            'placeholder'   => null,
            'required'      => true,
        ])
    </div>
    @include('components.form-groups.input-group', [
        'id'                => "from_date",
        'model'             => $model,
        'type'              => "date",
        'value'             =>  $model->from_date ? $model->from_date->format('Y-m-d') : now()->format('Y-m-d'),
        'label'             => "Ngày bắt đầu",
        'formClass'         => 'mb-3 col-md-2'
    ])
    @include('components.form-groups.input-group', [
        'id'                => "to_date",
        'model'             => $model,
        'type'              => "date",
        'value'             =>  $model->to_date ? $model->to_date->format('Y-m-d') : now()->format('Y-m-d'),
        'label'             => "Ngày kết thúc",
        'formClass'         => 'mb-3 col-md-2'
    ])
    @include('components.form-groups.input-group', [
        'id'                => "description",
        'model'             => $model,
        'type'              => "textarea",
        'label'             => "Mô tả",
        'formClass'         => 'mb-3 col-md-3',
        'placeholder'       => "Mô tả",
        'rows'              => 1,
    ])
</div>

@if ($model->isNew())
    @push('modals')
    <div class="modal fade" id="selectFeatureEventModal" data-bs-keyboard="true" tabindex="-1"
        aria-labelledby="selectFeatureEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="selectFeatureEventModalLabel">
                        Chọn tính năng
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-sm">
                    @foreach ($features as $key => $feature)
                        @if (in_array($key, ["e-1", "e-2", "e-3", "e-4"]))
                            <input type="hidden" name="features[]" value="{{ $key }}" form="event-detail-form">
                        @else
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="features[]" value="{{ $key }}"
                                                @checked(true) form="event-detail-form"
                                            >
                                                {{ $feature['name'] }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endpush
@endif
