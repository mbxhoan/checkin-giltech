

@if (!empty($label))
    <label class="form-label" for="{{ $fieldName }}">
        {{ $label }}
    </label>
@endif

<input
    type="file"
    id="{{ $id }}"
    name="{{ $fieldName }}"
    accept="image/*"
    @class([
        'form-control',
        !empty($className) ? $className : "",
        'is-invalid'    => $errors->has($fieldName)
    ])
    {{ !empty($required) && $required ? "required" : "" }}
    {{-- value="{{ old($fieldName, $model ?? ($value ?? null)) }}" --}}
    {{-- placeholder="{{ !empty($placeholder) ? $placeholder : "" }}" --}}
    {{ !empty($readonly) && $readonly ? "readonly" : "" }}
>

@error($fieldName)
    <span class="invalid-feedback">
        {{ $message }}
    </span>
@enderror
