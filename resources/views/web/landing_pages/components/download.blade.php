<div class="{{ $divClass }} text-center m-2">
    <div class="qrcode-download text-center">
        <a class="text-decoration-none {{ $btnClass ?? 'btn btn-danger' }} rounded"
            href="{{ $qrcode }}"
            alt="{{ $alt ?? "Qrcode" }}"
            download="{{ "{$qrcodeText}.png" }}"
        >
            {!! $btnText !!}
            <x-icon name="download" />
        </a>

        @if (isset($edit) && $edit)
            @include('admin.landing_pages._edit', [
                'id'            => $id,
                'text'          => $btnText,
                'language'      => $language,
                'eventId'       => $eventId,
                'model'         => $model,
            ])
        @endif
    </div>
</div>
