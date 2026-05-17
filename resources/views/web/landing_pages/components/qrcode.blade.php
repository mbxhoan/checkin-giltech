<div class="{{ $divClass }} text-center m-2">
    <div class="qrcode text-center">
        <img width="250px" src="{{ $qrcode }}" alt="{{ $alt ?? "Qrcode" }}" />
    </div>

    <div class="text-center text-xs">
        @if (isset($qrcodeText))
            <p class="thankyou">{{ $qrcodeText }}</p>
        @endif
    </div>
</div>
