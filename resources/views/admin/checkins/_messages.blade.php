<form action="{{ route('admin.events.update-custom_checkin_messages', $event) }}"
    method="POST"
    class="row my-2 px-2"
    id="event-{{ $event->id }}"
>
    @csrf
    @method('PUT')
    @include('components.form-groups.input-group', [
        'id'                => "event-{$event->id}",
        'fieldName'         => "custom_checkin_messages[$screen][$msg][msg]",
        'value'             => $customCheckinMessages[$screen][$msg]["msg"] ?? ($messages[$msg]['msg'] ?? null),
        'type'              => "text",
        'formClass'         => 'mb-2 col-md-12',
        'inputClass'        => "text-sm w-100 edit-change-field",
        // 'label'             => $msg,
        'placeholder'       => $msg,
    ])
    <div class="row mb-2">
        <input type="hidden" name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][bold]" value="0">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][bold]",
            'id'            => "event-{$event->id}",
            'label'         => "<b>Đậm</b>",
            'showLabelTop'  => true,
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "switch",
            'value'         => $customCheckinMessages[$screen][$msg]["bold"] ?? false,
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'form-check-input text-sm edit-change-field',
        ])
        <input type="hidden" name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][italic]" value="0">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][italic]",
            'id'            => "event-{$event->id}",
            'label'         => "<i>Nghiêng</i>",
            'showLabelTop'  => true,
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "switch",
            'value'         => $customCheckinMessages[$screen][$msg]["italic"] ?? false,
            'formClass'     => 'mb-0 col-md-3 px-0',
            'inputClass'    => 'form-check-input text-sm edit-change-field',
        ])
        <input type="hidden" name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][underline]" value="0">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][underline]",
            'id'            => "event-{$event->id}",
            'label'         => "<u>Gach</u>",
            'showLabelTop'  => true,
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "switch",
            'value'         => $customCheckinMessages[$screen][$msg]["underline"] ?? false,
            'formClass'     => 'mb-0 col-md-3 px-0',
            'inputClass'    => 'form-check-input text-sm edit-change-field',
        ])
        <input type="hidden" name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][show_info]" value="0">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][show_info]",
            'id'            => "event-{$event->id}",
            'label'         => "Hiển thị thông tin",
            'showLabelTop'  => true,
            'labelClass'    => 'form-check-label text-xs',
            'model'         => $event,
            'type'          => "switch",
            'value'         => $customCheckinMessages[$screen][$msg]["show_info"] ?? ($msg == 'success' ? true : false),
            'formClass'     => 'mb-0 col-md-3 px-0',
            'inputClass'    => 'form-check-input text-sm edit-change-field',
            'disabled'      => in_array($msg, [
                'success',
                'failed'
            ]) ? true : false,
        ])
    </div>
    <div class="row align-items-center mb-2">
        <input type="hidden" name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][bg]" value="0">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][bg]",
            'id'            => "event-{$event->id}",
            'label'         => "Nền",
            'showLabelTop'  => true,
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "switch",
            'value'         => $customCheckinMessages[$screen][$msg]["bg"] ?? false,
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'form-check-input text-sm edit-change-field',
        ])
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][bg_color]",
            'id'            => "event-{$event->id}",
            'label'         => "Màu nền",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "color",
            'value'         => $customCheckinMessages[$screen][$msg]["bg_color"] ?? "#ffffff",
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'form-control text-sm w-50 edit-change-field',
        ])
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][color]",
            'id'            => "event-{$event->id}",
            'label'         => "Màu chữ",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "color",
            'value'         => $customCheckinMessages[$screen][$msg]["color"] ?? "#000000",
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'form-control text-sm w-50 edit-change-field',
        ])
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][stroke]",
            'id'            => "event-{$event->id}",
            'label'         => "Viền chữ",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "color",
            'value'         => $customCheckinMessages[$screen][$msg]["stroke"] ?? "#ffffff",
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'form-control text-sm w-50 edit-change-field',
        ])
    </div>
    <div class="row px-2 mb-2">
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][font_size]",
            'id'            => "event-{$event->id}",
            'label'         => "Cỡ chữ",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "number",
            'value'         => $customCheckinMessages[$screen][$msg]["font_size"] ?? 20,
            'formClass'     => 'mb-0 col-md-3',
            'inputClass'    => 'text-sm w-100 edit-change-field',
        ])
        <div class="col-md-4">
            @include('components.select', [
                'labelClass'    => 'text-sm',
                'label'         => 'Font chữ',
                'fieldName'     => "custom_checkin_messages[$screen][$msg][font]",
                'id'            => "event-{$event->id}",
                'options'       => $event->getFonts(),
                'selected'      => $customCheckinMessages[$screen][$msg]["font"] ?? null,
                'placeholder'   => null,
                'formClass'     => 'text-sm edit-change-field w-100',
            ])
        </div>
    </div>
    <div class="row px-2">
        <div class="col-md-3 mb-2">
            @include('components.select', [
                'labelClass'    => 'text-sm',
                'label'         => 'Canh',
                'fieldName'     => "custom_checkin_messages[$screen][$msg][align]",
                'id'            => "event-{$event->id}",
                'options'       => $event->getAligns(),
                'selected'      => $customCheckinMessages[$screen][$msg]["align"] ?? null,
                'placeholder'   => null,
                'formClass'     => 'text-sm edit-change-field w-100',
            ])
        </div>
        <div class="col-md-3 mb-2">
            @include('components.select', [
                'labelClass'    => 'text-sm',
                'label'         => 'Độ rộng',
                'fieldName'     => "custom_checkin_messages[$screen][$msg][width]",
                'id'            => "event-{$event->id}",
                'options'       => $event->getWidths(),
                'selected'      => $customCheckinMessages[$screen][$msg]["width"] ?? 50,
                'placeholder'   => null,
                'formClass'     => 'text-sm edit-change-field w-100',
            ])
        </div>
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][pos_x]",
            'id'            => "event-{$event->id}",
            'label'         => "Canh ngang",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "number",
            'value'         => $customCheckinMessages[$screen][$msg]["pos_x"] ?? 0,
            'formClass'     => 'col-md-3 mb-2',
            'inputClass'    => 'text-sm w-100 edit-change-field',
        ])
        @include('components.form-groups.input-group', [
            'fieldName'     => "custom_checkin_messages[$screen][$msg][pos_y]",
            'id'            => "event-{$event->id}",
            'label'         => "Canh dọc",
            'labelClass'    => 'form-check-label text-sm',
            'model'         => $event,
            'type'          => "number",
            'value'         => $customCheckinMessages[$screen][$msg]["pos_y"] ?? 0,
            'formClass'     => 'col-md-3 mb-2',
            'inputClass'    => 'text-sm w-100 edit-change-field',
        ])
    </div>
    <div class="row px-2 mt-2">
        @include('components.form-groups.input-group', [
            'id'            => "event-{$event->id}",
            'fieldName'     => "custom_checkin_messages[$screen][$msg][link]",
            'placeholder'   => "Link ảnh thay thế",
            'model'         => $event,
            'value'         => $customCheckinMessages[$screen][$msg]["link"] ?? null,
            'type'          => "text",
            'formClass'     => "mb-2 col-md-12",
            'inputClass'    => "text-sm w-100 edit-change-field",
        ])
    </div>
</form>
