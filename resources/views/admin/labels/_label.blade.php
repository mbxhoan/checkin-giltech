<div id="to-print" style="">
    <link href="{{ asset('offlines/offline-css/css2.css') }}" rel="stylesheet">
    <style>
        #ms-label * {
            font-family: 'Open Sans', sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
    <div id="ms-label" class="border shadow bg-white"
        style="
            position: relative;
            width: {{ !empty($label->width) ? $label->width : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
            height: {{ !empty($label->height) ? $label->height : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
            {{ $label->rotate == 90 ? 'transform: translate(-10%, 20%) rotate(90deg);' : 'transform: rotate('.$label->rotate.'deg);' }}
        "
    >
    </div>
    @foreach ($cardDetails as $cardDetail)
        <div id="{{ $cardDetail->field }}-{{ $cardDetail->id }}"
            class="draggable {{ $cardDetail->type == $cardDetail::TYPE_FIELD ? "draggable-text-box" : "draggable-text-image" }}"
            data-target-pos_x='input#card-detail-{{ $cardDetail->id }}[name="pos_x"]'
            data-target-pos_y='input#card-detail-{{ $cardDetail->id }}[name="pos_y"]'
        >
            @if ($cardDetail->type == $cardDetail::TYPE_FIELD)
                {{ $cardDetail->field }}
            @else
                @if (in_array($cardDetail->field, [
                    "qrcode"
                ]))
                    <img src="{{ asset(config("info.placeholders.qrcode")) }}" width="100%" height="100%" alt="Qrcode">
                @endif
            @endif
        </div>
        {!! $cardDetail->generateCssFromAttributes($cardDetail->getCssAttributes(), "{$cardDetail->field}-{$cardDetail->id}") !!}
    @endforeach
</div>

<input type="hidden" id="pos_x" name="pos_x" value="0">
<input type="hidden" id="pos_y" name="pos_y" value="0">
<input type="hidden" id="url" value="{{ route('admin.cards.render-background', [
        'card'      => $card,
        'event'     => $event,
    ]) }}"
>

@push('admin_css')
    <style>
        .background-container {
            background-position: center center;
            background-size: cover; /* Use cover to fill the container, potentially cropping */
            background-repeat: no-repeat;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .draggable {
            position: absolute; /* Allows dragging relative to the parent */
            cursor: move; /* Changes cursor to indicate it's draggable */
            z-index: 10; /* Ensure it's above the background */
        }
        .draggable-text-image {
            position: absolute;
        }
        .draggable-text-box {
            transform: translateY(-50%);
        }
        .draggable-text-box:hover {
            background-color: rgba(209, 209, 209, 0.312);
            border-radius: 15px;
        }
    </style>
@endpush
