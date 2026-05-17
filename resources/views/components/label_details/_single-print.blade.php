<div id="to-print" style="{{ $display ? "" : "display: none;" }}">
    <link href="{{ asset('offlines/offline-css/css2.css') }}" rel="stylesheet">
    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap'); */
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Noto+Sans+Display:ital,wght@0,100..900;1,100..900&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap');

        #ms-label * {
            /* font-family: 'Open Sans', sans-serif !important;
            -webkit-print-color-adjust: exact !important; */
            print-color-adjust: exact !important;
        }
    </style>
    <div id="ms-label" class="border shadow bg-white"
        style="
            position: relative;
            width: {{ !empty($label->width) ? $label->width : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
            height: {{ !empty($label->height) ? $label->height : 0 }}{{ !empty($label->unit) ? $label->unit : 'mm' }};
            {{ $label->rotate == 90 ? 'transform: translate(-10%, 20%) rotate(90deg);' : 'transform: rotate('.$label->rotate.'deg);' }}
            overflow: hidden;
        "
    >
        @if (!empty($labelDetails) && $labelDetails->count())
            @php
                $fields = $client ? $client->getFullFieldValue() : [];
            @endphp
            @foreach ($labelDetails as $index => $labelDetail)
                @if ($labelDetail->status == $labelDetail::STATUS_ACTIVE)
                    <span class="draggable"
                        data-target-pos_x='input#label-detail-{{ $labelDetail->id }}[name="pos_x"]'
                        data-target-pos_y='input#label-detail-{{ $labelDetail->id }}[name="pos_y"]'
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
                            {{ $labelDetail->label_id == 8 && $labelDetail->field == "name" ? "border-bottom: 3px solid #000000; width: 75%;" : "" }}
                            {{ $labelDetail->label_id == 8 ? "font-family: 'Source Sans 3', sans-serif !important" : "" }}
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

                                {{-- @if ($client->avatar)
                                    <img src="{{ route('fr.file.access-avatar', [
                                            'qrcode' => $client->qrcode
                                        ]) }}" width="100%" alt="qrcode"
                                    >
                                @endif --}}

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
</div>
