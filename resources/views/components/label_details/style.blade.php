<div class="">
    <div id="font-link">
        <link href="{{ $label->font_link ?? "" }}" rel="stylesheet">
    </div>
    {{-- <input type="text" name="" id="font-link" value="{{ $label->font_link ?? "" }}"> --}}
    <style id="style">
        @media print {
            @page {
                /* width: {{ $label->width }}{{ $label->unit }};
                height: {{ $label->height }}{{ $label->unit }}; */
                /* size: {{ $label->width }}{{ $label->unit }} {{ $label->height }}{{ $label->unit }}; */
                margin: 0;
            }
            .label {

            }
        }
        @page {
            size: {{ $label->width }}{{ $label->unit }} {{ $label->height }}{{ $label->unit }};
        }
    </style>
</div>
