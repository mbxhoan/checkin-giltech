<input
    type="{{ $type }}"
    id="{{ $id }}"
    name="{{ $fieldName ?? $id }}"
    @class([($inputClass ?? 'form-check-input text-sm'), 'is-invalid' => $errors->has($id)])
    {{ $required ? "required" : "" }}
    {{ $readonly ? "readonly" : "" }}
    {{ $disabled ? "disabled" : "" }}
    @checked($checked ?? false)
    value="{{ $value ?? "" }}"
    data-url="{{ $changeUrl ?? null }}"
>
