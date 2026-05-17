<div class="form-group {{ $formClass ?? 'mb-3' }}">
    @switch($type)
        @case('checkbox')
            @if (!empty($label) && (isset($showLabelTop) && $showLabelTop))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
                    {!! $label !!}
                </label>
            @endif
            @include('components.form-groups.checkbox', [
                'id'            => $id,
                'type'          => "checkbox",
                'fieldName'     => $fieldName ?? null,
                'model'         => $model ?? null,
                'value'         => $value ?? null,
                'placeholder'   => $placeholder ?? null,
                'required'      => $required ?? false,
                'readonly'      => $readonly ?? false,
                'disabled'      => $disabled ?? false,
                'autofocus'     => $autofocus ?? false,
                'inputClass'    => $inputClass ?? false,
                'checked'       => $checked ?? false,
            ])
            @if (!empty($label) && (isset($showLabelTop) && !$showLabelTop))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
                    {!! $label !!}
                </label>
            @endif
        @break

        @case('radio')
            @if (!empty($label) && (isset($showLabelTop) && $showLabelTop))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
                    {!! $label !!}
                </label>
            @endif
            @include('components.form-groups.radio', [
                'id'            => $id,
                'type'          => "radio",
                'fieldName'     => $fieldName ?? null,
                'model'         => $model ?? null,
                'value'         => $value ?? null,
                'placeholder'   => $placeholder ?? null,
                'required'      => $required ?? false,
                'readonly'      => $readonly ?? false,
                'disabled'      => $disabled ?? false,
                'autofocus'     => $autofocus ?? false,
                'inputClass'    => $inputClass ?? false,
                'checked'       => $checked ?? false,
            ])
            @if (!empty($label) && (isset($showLabelTop) && !$showLabelTop))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
                    {!! $label !!}
                </label>
            @endif
        @break

        @case('switch')
            <div class="form-check form-switch">
                @if (!empty($label) && (isset($showLabelTop) && $showLabelTop))
                    <label class="{{ $labelClass ?? 'form-check-label' }}" for="{{ $id }}">
                        {!! $label !!}
                    </label>
                @endif
                @include('components.form-groups.switch', [
                    'id'            => $id,
                    'type'          => "checkbox",
                    'fieldName'     => $fieldName ?? null,
                    'model'         => $model ?? null,
                    'value'         => $value ?? null,
                    'required'      => $required ?? false,
                    'readonly'      => $readonly ?? false,
                    'disabled'      => $disabled ?? false,
                    'checked'       => ($value ?? ($checked ?? false)),
                    'changeUrl'     => $changeUrl ?? null,
                ])
                @if (!empty($label) && (isset($showLabelTop) && !$showLabelTop))
                    <label class="{{ $labelClass ?? 'form-check-label' }}" for="{{ $id }}">
                        {!! $label !!}
                    </label>
                @endif
            </div>
            @break

        @case('toggle')
            @if (!empty($label))
                <label class="{{ $labelClass ?? 'form-check-label form-label' }}" for="{{ $id }}">
                    {!! $label !!}
                </label>
            @endif
            <div class="form-check form-switch">
                @include('components.form-groups.switch', [
                    'id'            => $id,
                    'type'          => "checkbox",
                    'fieldName'     => $fieldName ?? null,
                    'model'         => $model ?? null,
                    'value'         => $value ?? null,
                    'required'      => $required ?? false,
                    'readonly'      => $readonly ?? false,
                    'disabled'      => $disabled ?? false,
                    'checked'       => $checked ?? false,
                    'changeUrl'     => $changeUrl ?? null,
                ])
            </div>
            @break

        @case('textarea')
            @if (!empty($label))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
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
            @include('components.form-groups.textarea', [
                'id'            => $id,
                'type'          => $type,
                'fieldName'     => $fieldName ?? null,
                'model'         => $model ?? null,
                'value'         => $value ?? null,
                'placeholder'   => $placeholder ?? null,
                'inputClass'    => $inputClass ?? null,
                'required'      => $required ?? false,
                'readonly'      => $readonly ?? false,
                'disabled'      => $disabled ?? false,
                'rows'          => $rows ?? null,
                'cols'          => $cols ?? null,
            ])
            @break

        @case('password')
            @if (!empty($label))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
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
            <div class="input-group">
                @include('components.form-groups.password', [
                    'id'            => $id,
                    'type'          => "password",
                    'fieldName'     => $fieldName ?? null,
                    'model'         => $model ?? null,
                    'value'         => $value ?? null,
                    'placeholder'   => $placeholder ?? null,
                    'inputClass'    => $inputClass ?? null,
                    'autofocus'     => $autofocus ?? false,
                    'required'      => $required ?? false,
                    'readonly'      => $readonly ?? false,
                    'disabled'      => $disabled ?? false,
                    'changeUrl'     => $changeUrl ?? null,
                    'autocomplete'  => $autocomplete ?? null,
                ])
                <span class="input-group-text" onclick="togglePasswordVisibility('{{ $id }}')">
                    <i class="fa fa-eye" id="togglePasswordIcon_{{ $id }}"></i>
                </span>
                @error($id)
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            @break

        @case("recaptcha")
            <div class="{{ $formClass ?? 'form-group' }}">
                <strong>{{ $label ?? null }}</strong>
                <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                @if ($errors->has("g-recaptcha-response"))
                    <span class="text-danger text-sm">
                        {{ $errors->first('g-recaptcha-response') }}
                    </span>
                @endif
            </div>
            @break

        @default
            @if (!empty($label))
                <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
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

            @include('components.form-groups.input', [
                'id'            => $id,
                'type'          => $type,
                'fieldName'     => $fieldName ?? null,
                'model'         => $model ?? null,
                'accept'        => $accept ?? null,
                'value'         => $value ?? null,
                'placeholder'   => $placeholder ?? null,
                'inputClass'    => $inputClass ?? null,
                'autofocus'     => $autofocus ?? false,
                'required'      => $required ?? false,
                'readonly'      => $readonly ?? false,
                'disabled'      => $disabled ?? false,
                'changeUrl'     => $changeUrl ?? null,
                'multiple'      => $multiple ?? false,
            ])
    @endswitch

    @error($id)
        <span class="invalid-feedback">
            {{ $message }}
        </span>
    @enderror
</div>
