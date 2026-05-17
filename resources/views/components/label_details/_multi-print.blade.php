<div id="multi-print" style="display: none;">
    <link href="{{ asset('offlines/offline-css/css2.css') }}" rel="stylesheet">
    <style>
        @font-face {
            /* font-family: 'AWSFont';
            src: url('{{ asset('assets/fonts/AWSFont/AmazonEmberDisplay_Rg.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal; */
        }
        .page-break {
            page-break-after: always !important;
        }
        .label {
            /* page-break-after: always !important; */
            display: grid;
            grid-template-rows: auto 1fr auto;
            align-items: start;
            break-inside: avoid;          /* modern */
            page-break-inside: avoid;     /* legacy */
            -webkit-region-break-inside: avoid;
            overflow: hidden;
        }
    </style>
    @foreach ($clients as $client)
        <div
            id="ms-label-{{ $client->id }}"
            class="border shadow bg-white ms-label"
            style="
                position: relative;
                width: {{ !empty($label->width) ? $label->width : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
                height: {{ !empty($label->height) ? $label->height : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
                {{ $label->rotate == 90 ? 'transform: translate(-10%, 20%) rotate(90deg);' : 'transform: rotate('.$label->rotate.'deg);' }}
                overflow: hidden;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            "
        >
            @if (!empty($labelDetails) && $labelDetails->count())
                @php
                    $fields = $client ? $client->getFullFieldValue() : [];
                @endphp
                @foreach ($labelDetails as $index => $labelDetail)
                    @if ($labelDetail->status == $labelDetail::STATUS_ACTIVE)
                        <span class=""
                            id="{{ $labelDetail->id }}"
                            style="
                                position: absolute;
                                font-family: 'Open Sans', sans-serif !important;
                                width: {{ $labelDetail->type == $labelDetail::TYPE_IMG ? $labelDetail->width."%" : $labelDetail->width."%" }};
                                height: {{ $labelDetail->type == $labelDetail::TYPE_IMG ? "auto" : "" }};
                                top: {{ $labelDetail->pos_y }}{{ $labelDetail->unit }};
                                left: {{ $labelDetail->pos_x }}{{ $labelDetail->unit }};
                                color: {{ $labelDetail->color }};
                                font-size: {{ $labelDetail->type == $labelDetail::TYPE_IMG ? "" : (($labelDetail->size*$label->height)/100).$label->unit }};
                                text-align: {{ $labelDetail->h_align }};
                                {{ $labelDetail->h_align == $labelDetail::H_ALIGN_CENTER ? "width: 95%;" : "" }}
                                {{ $labelDetail->bold ? "font-weight: bold;" : null }}
                                {{ $labelDetail->italic ? "font-style: italic;" : null }}
                            "
                        >
                            @switch ($labelDetail->type)
                                @case ($labelDetail::TYPE_IMG)
                                    @if ($labelDetail->field == "qrcode")
                                        @if ($client)
                                            @if ($client->img_qrcode)
                                                <img src="{{ route('clients.view-qrcode-by-id', [
                                                        'id' => $client->id
                                                    ]) }}" width="{{ $labelDetail->h_align == $labelDetail::H_ALIGN_CENTER ? $labelDetail->width."%" : "100%" }}" alt="{{ $client->qrcode }}"
                                                >
                                            @endif
                                        @else
                                            <img src="{{ asset(config("info.placeholders.qrcode")) }}" width="{{ $labelDetail->h_align == $labelDetail::H_ALIGN_CENTER ? $labelDetail->width."%" : "100%" }}" alt="qrcode">
                                        @endif
                                    @endif
                                    @break
                                @default
                                    @if ($client)
                                        @if (isset($fields[$labelDetail->field]))
                                            @php
                                                $fieldArray = explode("\n", $fields[$labelDetail->field]);
                                            @endphp
                                            @foreach ($fieldArray as $str)
                                                @php
                                                    if ($labelDetail->uppercase) {
                                                        $str = mb_strtoupper($str);
                                                    }
                                                @endphp
                                                @if (count($fieldArray) > 1)
                                                    {{ $str }}
                                                    <br>
                                                @else
                                                    {{ $str }}
                                                @endif
                                            @endforeach
                                        @else
                                            {{ "" }}
                                        @endif
                                    @else
                                        @if ($labelDetail->uppercase)
                                            {{ mb_strtoupper($labelDetail->field) }}
                                        @else
                                            {{ $labelDetail->field }}
                                        @endif
                                    @endif
                            @endswitch
                        </span>
                    @endif
                @endforeach
            @endif
        </div>
        <div class="page-break"></div>
    @endforeach
</div>
