

@if (!empty($label))
    <label class="form-label" for="{{ $fieldName }}">
        {{ $label }}
    </label>
@endif

<input
    type="{{ !empty($type) ? $type : "text" }}"
    id="{{ $id }}"
    name="{{ $fieldName }}"
    @class([
        'form-control',
        !empty($className) ? $className : "",
        'is-invalid'    => $errors->has($fieldName)
    ])
    {{ !empty($required) && $required ? "required" : "" }}
    value="{{ old($fieldName, $model ?? ($value ?? null)) }}"
    placeholder="{{ !empty($placeholder) ? $placeholder : "" }}"
    {{ !empty($readonly) && $readonly ? "readonly" : "" }}
    {{ !empty($autofocus) && $autofocus ? "autofocus" : "" }}
>

@error($fieldName)
    <span class="invalid-feedback">
        {{ $message }}
    </span>
@enderror
