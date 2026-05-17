<div class="p-2 bg-light rounded shadow-sm mb-2">
    <div class="row">
        <div class="col-md-10">
            @include('components.select', [
                'label'         => "Mẫu Quay số khác: ",
                'id'            => 'lucky_draw_id',
                'fieldName'     => 'lucky_draw_id',
                'formClass'     => 'w-50',
                'options'       => ($model->isNew() ? ["" => " - "] : []) + $luckyDraws
                    ->pluck('name', 'id')
                    ->toArray(),
                'selected'      => $model->id,
                'changeUrl'     => '',
            ])
        </div>
    </div>
    <h5>
        1. Thông tin:
    </h5>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "name",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->name,
            'label'             => "Thông tin",
            'formClass'         => "mb-3 col-md-6",
            'placeholder'       => 'Tên mẫu quay số',
            'required'          => true,
        ])
        <div class="mb-3 col-md-6">
            @include('components.select', [
                'label'         => "Loại",
                'id'            => 'type',
                'fieldName'     => 'type',
                'options'       => $model->getTypes(),
                'selected'      => $model->type,
            ])
        </div>
    </div>
</div>
<div class="p-2 bg-light rounded shadow-sm mb-2">
    <h5>
        2. Hình ảnh:
    </h5>
    <div class="row">
        @foreach ($model->getMediaFields() as $field => $attr)
            <div class="col-md-6">
                @if (count($attr))
                    @include('components.form-groups.input-group', [
                        'id'        => $field,
                        'label'     => $attr['label'],
                        'model'     => $model,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2'
                    ])
                    @if ($model->$field)
                        <div class="w-100 text-center">
                            <a href="{{ $attr['object']->getUrl() }}" class="w-100" target="_blank">
                                <img src="{{ $attr['object']->getUrl() }}" alt="{{ $attr['object']->name }}" width="100">
                            </a>

                        <div class="mt-2 text-center">
                                <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#{{ $field }}-{{ $model->id }}">
                                    <x-icon name="clipboard" prefix="fa-regular" />
                                </button>

                                <a href="{{ route('admin.media.show', $attr['object']) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                    <x-icon name="download" />
                                </a>
                        </div>

                        <input type="text" id="{{ $field }}-{{ $model->id }}" value="{{ $attr['object']->getUrl() }}" style="opacity: 0;">
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
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
