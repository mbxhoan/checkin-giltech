<textarea
    id="{{ $id }}"
    name="{{ $fieldName ?? $id }}"
    @class([($inputClass ?? 'form-control'), 'is-invalid' => $errors->has($id)])
    {{ $required ? "required" : "" }}
    {{ $readonly ? "readonly" : "" }}
    {{ $disabled ? "disabled" : "" }}
    {{ $rows ? "rows={$rows}" : "" }}
    {{ $cols ? "cols={$cols}" : "" }}
    placeholder="{{ $placeholder ?? "" }}"
>{{ ($value ?? null) ?? old($id, $model ?? null) }}</textarea>
