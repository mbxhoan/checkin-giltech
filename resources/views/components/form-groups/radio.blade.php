<input
    type="radio"
    id="{{ $id }}"
    name="{{ $fieldName ?? $id }}"
    @class([$inputClass ?? 'form-check-input form-check text-sm', 'is-invalid' => $errors->has($id)])
    value="{{ $value }}"
    {{ $autofocus ? "autofocus" : "" }}
    {{ $required ? "required" : "" }}
    {{ $readonly ? "readonly" : "" }}
    {{ $disabled ? "disabled" : "" }}
    @checked($checked ?? false)
/>