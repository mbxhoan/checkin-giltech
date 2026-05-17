<div id="{{ $field }}-{{ $model->id }}" class="edit-block">
    <span class="fw-bold">
        {{ $label ?? "" }}
    </span>
    <span class="value {{ !empty($isBold) && $isBold ? "fw-bold" : "" }}"
        data-label="{{ !empty($label) ? $label : "" }}"
    >
        {{ $model->$field }}
    </span>
    <a href="" id="{{ $field }}-{{ $model->id }}" class="text-primary btn-edit-field">
        <x-icon name="edit" />
    </a>
</div>
<div id="{{ $field }}-{{ $model->id }}" class="input-block" style="display: none">
    <input
        type="{{ !empty($type) ? $type : 'text' }}"
        id="edit-{{ $field }}-{{ $model->id }}"
        class=""
        value="{{ $model->$field }}"
    >
    <a id="{{ $field }}-{{ $model->id }}" href="" class="text-decoration-none text-md text-success btn-update-field"
        data-id="{{ $model->id }}"
        data-field="{{ $field }}"
        data-url="{{ $editFieldUrl }}"
    >
        <x-icon name="circle-check" />
    </a>
    <a id="{{ $field }}-{{ $model->id }}" href="" class="text-decoration-none text-md text-danger btn-cancel">
        <x-icon name="circle-xmark" />
    </a>
</div>
