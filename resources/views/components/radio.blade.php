<div class="{{ $formClass ?? 'form-group form-group-'.$fieldName }}">
    @if (!empty($label))
        <label class="form-label" for="{{ $id }}" id="{{ $id }}">
            {!! $label !!}
            @if (!empty($required) && $required)
                <span class="text-danger fw-bold fst-italic text-xs alert-required">
                    *
                </span>
            @endif
            @if (!empty($unique) && $unique)
                <span class="text-danger fw-bold fst-italic text-xs alert-unique">
                    !
                </span>
            @endif
        </label>
    @endif

    @error($id)
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror

    <div class="input-group-{{ $fieldName }}">
        @foreach ($options as $key => $val)
            <div id="radio-item-{{ ($key) }}" class="form-check pb-2">
                <label class="form-control-label">
                    <input
                        type="radio"
                        name="{{ $fieldName }}"
                        id="option_{{ $key }}"
                        class="{{ $inputClass ?? 'form-check-input' }}"
                        value="{{ $key }}"
                        {{ $key == old($id, $selected) ? 'checked' : '' }}
                    />
                    {{ $val }}
                </label>
            </div>
        @endforeach
    </div>
</div>
