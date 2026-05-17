<form
    action="{{ route('admin.event_settings.update-value', [
        'eventSetting' => $setting
    ]) }}"
    id="setting-{{ $setting->id }}"
    class="row mt-2 align-items-center"
    method="POST"
>
    @method('PUT')
    @csrf

    @include('components.form-groups.input-group', [
        'id'                => "setting-{$setting->id}",
        'fieldName'         => "event_id",
        'value'             => $event->id,
        'type'              => "hidden",
        'formClass'         => 'd-none',
    ])

    <div class="col-md-8 text-sm {{ isset($isChild) && $isChild ? "px-4" : "" }}" title="{{ $setting->name }}">
        {!! $setting->description !!}
        {{-- <a href="" class="text-xs btn-tutor"
            title="{{ $setting->description }}"
        >
            <x-icon name="circle-question" prefix="fa-solid fa-beat-fade" />
        </a> --}}
    </div>

    <div class="col-md-4 text-end">
        @switch($setting->input_type)
            @case($setting::INPUT_TYPE_SWITCH)
                @include('components.form-groups.input-group', [
                    'fieldName'     => "value",
                    'id'            => "setting-{$setting->id}",
                    'model'         => $setting,
                    'type'          => "switch",
                    'value'         => $setting->value,
                    'formClass'     => 'mb-0',
                    'inputClass'    => 'form-check-input text-sm setting-value',
                ])
                @break

            @case($setting::INPUT_TYPE_COLOR)
                @include('components.form-groups.input-group', [
                    'fieldName'     => "value",
                    'id'            => "setting-{$setting->id}",
                    'model'         => $setting,
                    'type'          => "color",
                    'value'         => $setting->value,
                    'formClass'     => 'w-50',
                    'inputClass'    => 'form-control text-sm setting-value',
                ])
                @break

            @case($setting::INPUT_TYPE_SELECT)
                @include('components.select', [
                    'fieldName'     => 'value',
                    'id'            => "setting-{$setting->id}",
                    'options'       => json_decode($setting->options, true),
                    'selected'      => $setting->value,
                    'formClass'     => 'form-control text-sm setting-value',
                ])
                @break

            @default
                @include('components.form-groups.input-group', [
                    'id'            => "setting-{$setting->id}",
                    'fieldName'     => "value",
                    'value'         => $setting->value,
                    'type'          => "text",
                    'inputClass'    => 'form-control text-sm setting-value',
                    'formClass'     => 'mb-0',
                    'preventEnter' => 'true',
                ])

        @endswitch
    </div>
</form>
