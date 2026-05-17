@if (!empty($label))
    <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
        {!! $label !!}
        @if (!empty($required) && $required)
            <span class="text-danger fw-bold fst-italic text-xs alert-required">
                (*)
            </span>
        @endif
        @if (!empty($unique) && $unique)
            <span class="text-danger fw-bold fst-italic text-xs alert-unique">
                !
            </span>
        @endif
    </label>
@endif

<select
    name="{{ $fieldName }}"
    id="{{ $id }}"
    @class([($formClass ?? 'form-control'), 
        'js-select2',
        'is-invalid' => $errors->has($id)])
    {{ !empty($disabled) && $disabled ? "disabled" : "" }}
    {{ !empty($required) && $required ? "required" : "" }}
    data-url="{{ $changeUrl ?? "" }}"
>
    @if (!empty($placeholder))
        <option value="">
            {{ $placeholder }}
        </option>
    @endif

    <option value="" selected disabled hidden>&nbsp;</option>
    </option>
    @foreach ($options as $id => $value)
        <option value="{{ $id }}"
            {{ !empty($selected) && $selected == $id ? "selected" : "" }}
            {{-- @selected(old($fieldName, $model ?? null) == $id) --}}
        >
            {{ $value }}
        </option>
    @endforeach
</select>

@error($id)
    <span class="invalid-feedback">{{ $message }}</span>
@enderror

@push('admin_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.js-select2').select2({
                width: '100%',
                placeholder: '',
                allowClear: false,
                language: {
                    noResults: function() {
                        return "Không tìm thấy kết quả nào";
                    }
                }
            });
        });
    </script>
@endpush
@push('admin_css')
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }
    </style>
@endpush