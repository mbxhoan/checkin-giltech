<div class="p-2 bg-light rounded shadow-sm mb-2">
    <div class="row">
        <div class="col-md-10">
            @include('components.select', [
                'label'         => "Mẫu tem khác: ",
                'id'            => 'label_id',
                'fieldName'     => 'label_id',
                'formClass'     => 'w-50',
                'options'       => ($model->isNew() ? ["" => " - "] : []) + $labels
                    ->pluck('name', 'id')
                    ->toArray(),
                'selected'      => $model->id,
                'changeUrl'     => '',
            ])
        </div>
        @if (!$model->isNew())
            <input type="hidden" name="" id="url" value="{{ route('admin.labels.update-live', $model) }}">
            <div class="col-md-2 text-end">
                <a href="" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#cloneLabelModal">
                    Clone
                    <x-icon name="clone"></x-icon>
                </a>
            </div>
        @endif
    </div>
    <h5>
        1. Thông tin:
    </h5>
    <div class="row">
        @include('components.form-groups.input-group', [
            'fieldName'     => "is_default",
            'id'            => "is_default",
            'label'         => "Mặc định",
            'showLabelTop'  => true,
            'labelClass'    => 'form-label form-check-label',
            'model'         => $model,
            'type'          => "switch",
            'value'         => $model->is_default,
            'formClass'     => 'mb-2 col-md-6',
            'inputClass'    => 'form-check-input',
        ])
    </div>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "name",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->name,
            'label'             => "Thông tin",
            'formClass'         => "mb-3 col-md-6",
            'placeholder'       => 'Tên mẫu in',
            'required'          => true,
        ])
        <div class="mb-3 col-md-6">
            @include('components.select', [
                'label'         => "Nhóm khách",
                'id'            => 'type',
                'fieldName'     => 'type',
                'options'       => ["" => "- Tất cả -"] + $types,
                'selected'      => $model->type,
            ])
        </div>
    </div>
</div>
<div class="p-2 bg-light rounded shadow-sm mb-2">
    <h5>
        2. Mẫu in:
    </h5>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "width",
            'model'             => $model,
            'value'             => $model->width ?? 5,
            'type'              => "number",
            'label'             => "Chiều dài",
            'formClass'         => 'mb-3 col-md-4',
            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
            'placeholder'       => "10",
            'required'          => true,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "height",
            'model'             => $model,
            'value'             => $model->height ?? 5,
            'type'              => "number",
            'label'             => "Chiều cao",
            'formClass'         => 'mb-3 col-md-4',
            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
            'placeholder'       => "10",
            'required'          => true,
        ])
        @include('components.form-groups.input-group', [
            'id'                => "unit",
            'model'             => $model,
            'value'             => $model->unit ?? "cm",
            'type'              => "text",
            'label'             => "Đơn vị",
            'formClass'         => 'mb-3 col-md-4',
            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
            'placeholder'       => "cm",
            'required'          => true,
        ])
    </div>
</div>
@include('components.form-groups.input-group', [
    'id'                => "id",
    'fieldName'         => "id",
    'value'             => $model->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "event_id",
    'fieldName'         => "event_id",
    'value'             => $event->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "status",
    'fieldName'         => "status",
    'value'             => $model->isNew() ? $model::STATUS_NEW : $model->status,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
