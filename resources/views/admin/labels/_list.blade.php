<div class="collapse p-2 bg-light rounded shadow-sm" id="collapseLabels">
    <div class="row mb-2">
        <div class="col-md-1 fw-bold text-sm text-center">
            #
        </div>
        <div class="col-md-4 fw-bold text-sm">
            Thông tin
        </div>
        <div class="col-md-6 fw-bold text-sm">

        </div>
    </div>

    @if (!empty($labels) && $labels->count())
        @foreach ($labels as $index => $label)
            <form action=""
                id="label-{{ $label->id }}"
                class="mb-1 py-1  {{ $label->is_default ? "bg-light" : "" }}"
                method="POST"
            >
                @method('PUT')
                @csrf
                @include('components.form-groups.input-group', [
                    'id'                => "custom-field-template-{$label->id}",
                    'fieldName'         => "event_id",
                    'value'             => $event->id,
                    'type'              => "hidden",
                    'formClass'         => 'd-none',
                ])
                <div class="fw-bold mb-2 col-md-1 text-sm text-center">
                    {{ ++$index }}
                </div>
                <div class="col-md-4 text-sm">
                    <a class="fst-italic" target="_blank" href="{{ route('admin.labels.edit', $label) }}"
                        id="{{ $label->id }}"
                    >
                        {{ $label->name }}
                    </a>
                </div>
                <div class="col-md-4">
                    @include('components.select', [
                        'label'         => null,
                        'fieldName'     => 'status',
                        'id'            => 'status',
                        'options'       => $label->getStatues(),
                        'selected'      => $label->status,
                        'formClass'     => 'text-sm w-100',
                    ])
                </div>
                <div class="col-md-3 text-end">
                    {{-- <button type="submit" class="btn btn-xs btn-primary">
                        <x-icon name="save" />
                        Lưu
                    </button> --}}

                    <a href="{{ route('admin.labels.edit', $label) }}" target="_blank"
                        class="btn btn-xs btn-primary"
                    >
                        <x-icon name="edit" />
                    </a>

                    @if (!$label->is_default)
                        {{-- <a href="" id="{{ $label->id }}"
                            class="btn btn-xs btn-danger btn-del-template"
                            data-id="custom-field-template-{{ $label->id }}"
                            data-url="{{ route('admin.custom_field_templates.destroy', [
                                'custom_field_template' => $label
                            ]) }}"
                        >
                            <x-icon name="trash" />
                        </a> --}}
                    @endif
                </div>
            </form>
        @endforeach
    @else
        <div class="fst-italic text-sm mb-2">
            Chưa có
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.labels.create', $event) }}" class="btn btn-xs btn-primary w-100">
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm mới
            </a>
        </div>
    </div>
</div>

