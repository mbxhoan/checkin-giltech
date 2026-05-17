<div class="p-2 bg-light rounded shadow-sm mb-2">
    <div class="row">
        <div class="col-md-12">
            @include('components.select', [
                'label'         => "Thư/Thiệp mời khác: ",
                'id'            => 'card_id',
                'fieldName'     => 'card_id',
                'formClass'     => 'w-50',
                'options'       => $cards
                    ->pluck('code', 'id')
                    ->toArray(),
                'selected'      => $model->id,
                'changeUrl'     => '',
            ])
        </div>
    </div>
    <a class="text-decoration-none text-dark"
        data-bs-toggle="collapse"
        href="#collapseInformation"
        aria-controls="collapseInformation"
    >
        <h5>
            1. Thông tin:
        </h5>
    </a>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "code",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->code,
            'label'             => "Thông tin",
            'formClass'         => "mb-3 col-md-6",
            'placeholder'       => 'code',
            'required'          => true,
        ])
        <div class="mb-3 col-md-6">
            @include('components.select', [
                'label'         => "Nhóm khách",
                'id'            => 'client_type',
                'fieldName'     => 'client_type',
                'options'       => ["" => "- Tất cả -"] + $types,
                'selected'      => $model->client_type,
            ])
        </div>
    </div>
</div>

<div class="p-2 bg-light rounded shadow-sm mb-2">
    <h5>
        2. Background & Output:
    </h5>
    <div class="row">
        <div class="col-md-4">
            @include('components.form-groups.input-group', [
                'id'        => "background",
                'label'     => "Background",
                'model'     => $model,
                'type'      => "file",
                'accept'    => ".png, .jpg, .jpeg",
                'formClass' => 'mb-2'
            ])
            @if ($model->background)
                <div class="w-100 text-center">
                    <a href="{{ !empty($model->backgroundUrl) ? $model->backgroundUrl->getUrl() : "#" }}" class="w-100" target="_blank">
                        <img src="{{ !empty($model->backgroundUrl) ? $model->backgroundUrl->getUrl() : "" }}" alt="{{ !empty($model->backgroundUrl) ? $model->backgroundUrl->name : $model->code }}" width="100">
                    </a>
                    <div class="mt-2 text-center">
                        <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#background-{{ $model->id }}">
                            <x-icon name="clipboard" prefix="fa-regular" />
                        </button>
                        <a href="{{ !empty($model->backgroundUrl) ? route('admin.media.show', $model->backgroundUrl) : "#" }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                            <x-icon name="download" />
                        </a>
                    </div>
                    <input type="text" id="background-{{ $model->id }}" value="{{ !empty($model->backgroundUrl) ? $model->backgroundUrl->getUrl() : "" }}" style="opacity: 0;">
                </div>
            @endif
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "file_name_template",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Tên file",
            'formClass'         => 'mb-3 col-md-4',
            'placeholder'       => "<qrcode>",
        ])
        <div class="mb-3 col-md-4">
            @include('components.select', [
                'label'         => "Định dạng",
                'id'            => 'extension',
                'fieldName'     => 'extension',
                'options'       => $model->getExtensions(),
                'selected'      => $model->extension,
            ])
        </div>
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
    'id'                => "event_code",
    'fieldName'         => "event_code",
    'value'             => $event->code,
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
