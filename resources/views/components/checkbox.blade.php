
{{-- labelPos -> START / END / TOP --}}

@if ((!empty($labelPos) && $labelPos == "START"))
    @if (!empty($label))
        <label class="form-label" for="{{ $fieldName }}">
            {{ $label }}
        </label>
    @endif
@endif

@if (!empty($label))
<label class="form-label" for="{{ $fieldName }}">
    {{ $label }}
</label>
@endif

<input
    type="checkbox"
    id="{{ $id }}"
    name="{{ $fieldName }}"
    @class([
        'is-invalid'    => $errors->has($fieldName),
        !empty($className) ? $className : "",
    ])
    value="{{ !empty($defaultValue) ? $defaultValue : 1 }}"
    {{ !empty($readonly) && $readonly ? "readonly" : "" }}
    {{ !empty($disabled) && $disabled ? "disabled" : "" }}
    {{ !empty($checked) && $checked ? "checked" : "" }}
>

@if ((!empty($labelPos) && $labelPos == "END"))
    @if (!empty($label))
        <label class="form-label" for="{{ $fieldName }}">
            {{ $label }}
        </label>
    @endif
@endif

@error($fieldName)
    <span class="invalid-feedback">
        {{ $message }}
    </span>
@enderror
