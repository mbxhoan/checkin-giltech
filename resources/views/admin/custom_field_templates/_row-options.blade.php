<div class="row px-2">
    <div class="col-md-1"></div>
    <div class="col-md-3 text-xs">Key</div>
    <div class="col-md-3 text-xs">Giá trị</div>
</div>

@if ($customFieldTemplate->options)
    @foreach (json_decode($customFieldTemplate->options, true) as $key => $attr)
        <div class="row px-2 mb-2 existed-option" id="{{ $key }}">
            <div class="col-md-1"></div>

            @include('components.form-groups.input-group', [
                'fieldName'     => "options[{$key}][key]",
                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                'type'          => "text",
                'value'         => $attr['key'] ?? null,
                'formClass'     => 'mb-0 col-md-3',
                'inputClass'    => "text-sm edit-change-field w-100",
            ])

            @include('components.form-groups.input-group', [
                'fieldName'     => "options[{$key}][value]",
                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                'type'          => "text",
                'value'         => $attr['value'] ?? null,
                'formClass'     => 'mb-0 col-md-3',
                'inputClass'    => "text-sm edit-change-field w-100",
            ])

            <div class="col-md-3">
                <a href="" class="text-xs text-danger btn-remove-option" data-id="custom-field-template-{{ $customFieldTemplate->id }}" id="{{ $key }}">
                    <x-icon name="trash" />
                </a>
            </div>
        </div>
    @endforeach
@endif

<div class="row px-2 mb-2 add-option">
    <div class="col-md-1"></div>

    @include('components.form-groups.input-group', [
        'fieldName'     => "options[".(isset($key) ? (int)$key + 1 : 0)."][key]",
        'id'            => "custom-field-template-{$customFieldTemplate->id}",
        'type'          => "text",
        'value'         => null,
        'formClass'     => 'mb-0 col-md-3',
        'inputClass'    => "text-sm edit-change-field w-100",
        'placeholder'   => 'Key',
    ])

    @include('components.form-groups.input-group', [
        'fieldName'     => "options[".(isset($key) ? (int)$key + 1 : 0)."][value]",
        'id'            => "custom-field-template-{$customFieldTemplate->id}",
        'type'          => "text",
        'value'         => null,
        'formClass'     => 'mb-0 col-md-3',
        'inputClass'    => "text-sm edit-change-field w-100",
        'placeholder'   => 'Giá trị',
    ])

    <div class="col-md-3">
        <a href="" class="text-xs text-danger btn-remove-option d-none"
            data-id="custom-field-template-{{ $customFieldTemplate->id }}"
            id="{{ isset($key) ? (int)$key + 1 : 0 }}"
        >
            <x-icon name="trash" />
        </a>

        <a href="" class="text-xs btn-add-option" id="">
            <x-icon name="plus-square" prefix="fa-regular"/>
        </a>
    </div>
</div>
