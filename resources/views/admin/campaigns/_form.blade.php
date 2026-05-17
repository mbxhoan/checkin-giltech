<div class="row">
    <div class="col-md-8">
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "",
                'value'             => $event->name,
                'type'              => "text",
                'label'             => "Sự kiện",
                'formClass'         => 'mb-3 col-md-4',
                'readonly'          => true,
            ])
            <div class="col-md-4 mb-3">
                @include('components.select', [
                    'label'         => "Email gửi",
                    'id'            => 'from_email',
                    'fieldName'     => 'from_email',
                    'options'       => $fromEmails,
                    'selected'      => $model->from_email,
                    'required'      => true,
                ])
            </div>
            {{-- @sys_admin
                <div class="col-md-4 mb-3">
                    @include('components.select', [
                        'label'         => "Email gửi",
                        'id'            => 'from_email',
                        'fieldName'     => 'from_email',
                        'options'       => $fromEmails,
                        'selected'      => $model->from_email,
                        'required'      => true,
                    ])
                </div>
            @else
                @include('components.form-groups.input-group', [
                    'id'                => "from_email",
                    'model'             => $model,
                    'type'              => "text",
                    'label'             => "Email gửi",
                    'formClass'         => 'mb-3 col-md-4',
                    'placeholder'       => "Email",
                    'required'          => true,
                ])
            @endsys_admin --}}
            {{-- @include('components.form-groups.input-group', [
                'id'                => "name",
                'model'             => $model,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ]) --}}
            @include('components.form-groups.input-group', [
                'id'                => "from_name",
                'value'             => $model->from_name ?? ($model->from_email ? $fromNames[$model->from_email] : null),
                'type'              => "hidden",
                'label'             => "Tên gửi",
                'formClass'         => 'd-none mb-3 col-md-4',
                'placeholder'       => "Tên",
            ])
            {{-- @include('components.form-groups.input-group', [
                'id'                => "subject",
                'model'             => $model,
                'type'              => "text",
                'label'             => 'Tiêu đề',
                'formClass'         => 'mb-3 col-md-4',
                'placeholder'       => 'Tiêu đề',
                'required'          => true,
            ]) --}}
        </div>
        <div class="row">
            <div class="mb-3 col-md-4">
                @include('components.select', [
                    'label'         => "Nhóm khách",
                    'id'            => 'type',
                    'fieldName'     => 'type',
                    'options'       => ["" => "- Tất cả -"] + $types,
                    'selected'      => $model->type,
                ])
            </div>
            <div class="mb-3 col-md-4">
                @include('components.select', [
                    'label'         => "Nội dung mail",
                    'id'            => 'template_id',
                    'fieldName'     => 'template_id',
                    'options'       => $templates,
                    'selected'      => $model->template_id,
                ])
            </div>
        </div>
    </div>
    <div class="col-md-4">
        {{-- <div class="row">
            <div class="mb-3 col-md-12">
                @include('components.select', [
                    'label'         => "Trạng thái",
                    'id'            => 'status',
                    'fieldName'     => 'status',
                    'options'       => $model->getStatues(),
                    'selected'      => $model->status,
                ])
            </div>
        </div> --}}
        @sys_admin
            {{-- <div class="row">
                @include('components.form-groups.input-group', [
                    'id'                => "limitation_per_time",
                    'model'             => $model,
                    'type'              => "number",
                    'label'             => 'Giới hạn gửi',
                    'formClass'         => 'mb-3 col-md-12',
                ])
            </div> --}}
            {{-- @include('components.form-groups.input-group', [
                'id'                => "limitation_per_time",
                'fieldName'         => "limitation_per_time",
                'value'             => 5,
                'type'              => "hidden",
                'formClass'         => "d-none",
            ]) --}}
            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'                => "hold_time",
                    'model'             => $model,
                    'type'              => "number",
                    'label'             => 'Thời gian giãn cách (s)',
                    'formClass'         => 'mb-3 col-md-12',
                ])
            </div>
        @endsys_admin
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "cc",
                'value'             => $model->cc ? implode(', ', json_decode($model->cc, true)) : null,
                'type'              => "text",
                'label'             => 'cc',
                'formClass'         => 'mb-3 col-md-12',
                'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
            ])
        </div>
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "bcc",
                'value'             => $model->bcc ? implode(', ', json_decode($model->bcc, true)) : null,
                'type'              => "text",
                'label'             => 'bcc',
                'formClass'         => 'mb-3 col-md-12',
                'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
            ])
        </div>
    </div>


    {{-- @include('components.form-groups.input-group', [
        'id'                => "phone",
        'model'             => $model,
        'type'              => "text",
        'label'             => 'Số điện thoại',
        'formClass'         => 'mb-3 col-md-2',
        'placeholder'       => 'Số điện thoại',
    ]) --}}
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
@include('components.form-groups.input-group', [
    'id'                => "is_online",
    'fieldName'         => "is_online",
    'value'             => 1,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
