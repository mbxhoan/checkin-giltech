<input
    type="{{ $type ?? "text" }}"
    id="{{ $id }}"
    name="{{ $fieldName ?? $id }}"
    @class([$inputClass ?? 'form-check-input form-check text-sm', 'is-invalid' => $errors->has($id)])
    value="1"
    {{-- {{ $value ? "checked" : null }} --}}
    {{ $autofocus ? "autofocus" : "" }}
    {{ $required ? "required" : "" }}
    {{ $readonly ? "readonly" : "" }}
    {{ $disabled ? "disabled" : "" }}
    @checked($checked ?? false)
>
