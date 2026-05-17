<input
    type="{{ $type ?? "text" }}"
    id="{{ $id }}"
    name="{{ $fieldName ?? $id }}"
    accept="{{ $accept ?? "" }}"
    @class([($inputClass ?? 'form-control'), 'is-invalid' => $errors->has($id)])
    value="{{ ($value ?? null) ?? old($id, $model ?? null) }}"
    {{ $required ? "required" : "" }}
    {{ $readonly ? "readonly" : "" }}
    {{ $disabled ? "disabled" : "" }}
    {{ $autofocus ? "autofocus" : "" }}
    placeholder="{{ $placeholder ?? "" }}"
    data-url="{{ $changeUrl ?? null }}"
    {{ $type == "number" ? 'step=any' : null }}
    multiple="{{ $multiple ? "multiple" : "" }}"
    @if (!empty($preventEnter)) onkeydown="if(event.key === 'Enter'){event.preventDefault(); return false;}" @endif
>
