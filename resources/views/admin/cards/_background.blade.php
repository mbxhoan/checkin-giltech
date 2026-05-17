<div class="background-container" style="
        background-image: url('{{ $mainBg }}');
        width: 100%;
        {{ !empty($width) && !empty($height) ? "aspect-ratio: {$width}/{$height};" : null }}
    "
>
    @foreach ($cardDetails as $cardDetail)
        <div id="{{ $cardDetail->field }}-{{ $cardDetail->id }}"
            class="{{ $cardDetail->type == $cardDetail::TYPE_FIELD ? "text-box" : "draggable-text-image" }}"
            data-target-pos_x='input#card-detail-{{ $cardDetail->id }}[name="pos_x"]'
            data-target-pos_y='input#card-detail-{{ $cardDetail->id }}[name="pos_y"]'
        >@if ($cardDetail->type == $cardDetail::TYPE_FIELD){{ $cardDetail->field }}@else
                @if (in_array($cardDetail->field, [
                    "qrcode"
                ]))
                    <img src="{{ asset(config("info.placeholders.qrcode")) }}" width="100%" height="100%" alt="Qrcode">
                @endif
            @endif</div>
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

@push('admin_js')
    <script>
        // function setFontSize(percent = 50) {
        //     // const container = document.getElementById('mother');
        //     const container = document.querySelector('.background-container');
        //     const text = document.getElementById('name-7');

        //     const containerHeight = container.offsetHeight;
        //     const fontSize = (containerHeight * percent) / 100;
        //     text.style.fontSize = `${fontSize}px`;
        // }

        // window.addEventListener('load', () => setFontSize(50));
        // window.addEventListener('resize', () => setFontSize(50));

        function getImageAspectRatio(imageUrl, callback) {
            const img = new Image();
            img.src = imageUrl;

            img.onload = function () {
                const aspectRatio = img.width / img.height;
                callback(aspectRatio, img.width, img.height);
            };

            img.onerror = function () {
                console.error('Failed to load image:', imageUrl);
                callback(null);
            };
        }

        const imageUrl = @json($mainBg);

        getImageAspectRatio(imageUrl, function(aspectRatio, width, height) {
            console.log(`Aspect Ratio: ${aspectRatio}`);
            console.log(`Image Dimensions: ${width}x${height}`);

            const container = document.querySelector('.background-container');
            if (container && aspectRatio) {
                container.style.aspectRatio = `${width} / ${height}`;
            }
        });
    </script>
@endpush

@push('admin_css')
    <style>
        .background-container {
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .draggable {
            position: absolute; /* Allows dragging relative to the parent */
            /* cursor: move;  */
            z-index: 10; /* Ensure it's above the background */
        }
        .text-box {
            position: absolute;
            transform: translateY(-50%);
            z-index: 10;
        }
        .draggable-text-image {
            position: absolute;
        }
        .text-box:hover {
            background-color: rgba(209, 209, 209, 0.312);
            border-radius: 15px;
        }
    </style>
@endpush
