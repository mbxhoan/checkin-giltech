@php
    $col = $screen == "desktop" ? 'is_checkin_desktop' : 'is_checkin_mobile';
    $customFieldTemplates = $customFieldTemplates->where($col, true);
@endphp

<div class="background-container" style="
        background-image: url('{{ $mainBg }}');
        {{ $screen == "desktop" ? "aspect-ratio: 1920/1080;" : "aspect-ratio: 1080/1920;" }}
    "
>
    @if ($msg && $msg != "none")
        <div id="msg-{{ $msg }}"
            class="draggable draggable-text-box"
            data-target-pos_x='input#event-{{ $event->id }}[name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][pos_x]"]'
            data-target-pos_y='input#event-{{ $event->id }}[name="custom_checkin_messages[{{ $screen }}][{{ $msg }}][pos_y]"]'
        >
            @if (isset($customCheckinMessages[$screen][$msg]['link']))
                <img
                    src="{{ $customCheckinMessages[$screen][$msg]['link'] }}"
                    width="100%"
                    alt="{{ $customCheckinMessages[$screen][$msg]['msg'] ?? $messages[$msg]['msg'] }}"
                >
            @else
                {{ $customCheckinMessages[$screen][$msg]['msg'] ?? $messages[$msg]['msg'] }}
            @endif
            {!! $event->generateCssFromAttributes($customCheckinMessages[$screen] ?? [], "msg-{$msg}", $msg) !!}
        </div>
    @endif
    @if (($customCheckinMessages[$screen][$msg]['show_info'] ?? false
    || in_array($msg, [
        'success'
    ])) || (empty($msg) || in_array($msg, [
        "none"
    ])))
        @foreach ($customFieldTemplates as $customFieldTemplate)
            <div id="{{ $customFieldTemplate->name }}"
                class="draggable draggable-text-box"
                data-target-pos_x='input#custom-field-template-{{ $customFieldTemplate->id }}[name="checkins[{{ $screen }}][pos_x]"]'
                data-target-pos_y='input#custom-field-template-{{ $customFieldTemplate->id }}[name="checkins[{{ $screen }}][pos_y]"]'
            >
                @switch($customFieldTemplate->type)
                    @case($customFieldTemplate::TYPE_IMAGE)
                        <img
                            src="{{ asset(config("info.placeholders.image")) }}"
                            width="100%"
                            alt="{{ $customFieldTemplate->description ?? $customFieldTemplate->name }}"
                            style="border: 2px solid orange; border-radius: 10px;"
                        >
                        @break

                    @default
                        {{-- text --}}
                        @if ($customFieldTemplate->show_prefix)
                            {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}:
                        @else
                            {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}
                        @endif
                @endswitch
            </div>
            {!! $customFieldTemplate->generateCssFromAttributes($customFieldTemplate->checkins ?? [], $customFieldTemplate->name, $screen) !!}
        @endforeach
    @endif
</div>

<input type="hidden" id="pos_x" name="pos_x" value="0">
<input type="hidden" id="pos_y" name="pos_y" value="0">
<input type="hidden" id="screen" value="{{ $screen }}">
<input type="hidden" id="url" value="{{ route('admin.checkins.render-background', [
        'event'     => $event,
        'screen'    => $screen,
        'msg'       => $msg,
    ]) }}"
>

{{-- @push('admin_js')
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const draggableCheckin = () => {
                const $container = $('.background-container');
                const $draggables = $('#backgroundContainer .draggable-text-box');

                // $draggables.draggable('destroy');
                $draggables.each(function () {
                    const $el = $(this);

                    // ✅ Only destroy if already initialized
                    if ($el.data("ui-draggable")) {
                        $el.draggable('destroy');
                    }
                });

                $draggables.draggable({
                    // containment: $container,
                    drag: function (event, ui) {
                        const containerWidth = $container.width();
                        const containerHeight = $container.height();
                        const draggablePosition = ui.position;
                        const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
                        const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

                        let pos_x = $(this).data('target-pos_x');
                        let pos_y = $(this).data('target-pos_y');
                        const $inputLeft = $(pos_x);
                        const $inputTop = $(pos_y);

                        $inputLeft.val(percentageLeft.toFixed(2));
                        $inputTop.val(percentageTop.toFixed(2));
                    },
                    stop: function (event, ui) {
                        const containerWidth = $container.width();
                        const containerHeight = $container.height();
                        const draggablePosition = ui.position;
                        const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
                        const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

                        let pos_x = $(this).data('target-pos_x');
                        let pos_y = $(this).data('target-pos_y');
                        const $inputLeft = $(pos_x);
                        const $inputTop = $(pos_y);

                        $inputLeft.val(percentageLeft.toFixed(2));
                        $inputTop.val(percentageTop.toFixed(2)).trigger('change');
                        console.log('Stopped dragging - Left:', percentageLeft.toFixed(2) + '%', 'Top:', percentageTop.toFixed(2) + '%');
                    }
                });
            }

            draggableCheckin();
        });
    </script>
@endpush --}}

@push('admin_css')
    <style>
        /* CSS for the background container */
        .background-container {
            /* Your existing background styles */
            background-position: center center;
            background-size: cover; /* Use cover to fill the container, potentially cropping */
            /* background-attachment: fixed; */
            background-repeat: no-repeat;

            /* Make it responsive and maintain aspect ratio */
            width: 100%;
            /* IMPORTANT: Set the aspect ratio based on your background image dimensions (width / height) */
            /* Replace 1920 and 1080 with the actual dimensions of your desktop background image */
            /* If you need to support older browsers, use the padding hack instead of aspect-ratio */
            /* Example Padding Hack (if image is 1920x1080): */
            /* height: 0; */
            /* padding-top: calc(1080 / 1920 * 100%); */

            /* Position relative is essential for absolute positioning of the draggable child */
            position: relative;

            /* Ensure draggable items stay inside */
            overflow: hidden;

            /* Optional: Add a min-height if needed, but aspect-ratio handles height */
            /* min-height: 200px; */
        }

        /* CSS for the draggable text box */
        .draggable-text-box {
            position: absolute; /* Allows dragging relative to the parent */
            cursor: move; /* Changes cursor to indicate it's draggable */
            z-index: 10; /* Ensure it's above the background */
        }
        .draggable-text-box:hover {
            background-color: rgba(209, 209, 209, 0.312);
            border-radius: 15px;
        }
    </style>
@endpush
