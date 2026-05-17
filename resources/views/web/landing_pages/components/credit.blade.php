@php
    $info = config('info');
    $credit = $info['credits'];
    $defaultLogo = $info['page']['logo_1']['internal_path'];
@endphp

<div class="credit-info p-4" style="
        height: 200px;
    "
>
    <div class="text-center mt-2" style="font-size: 12px;">
        <div class="">
            @if (!empty($logo))
                <img src="{{ $logo }}" alt="logo" width="125px">
            @else
                <img src="{{ asset($defaultLogo) }}" alt="logo" width="125px">
            @endif
        </div>
        <div class="mt-5">
            <p class="text-sm text-muted" style="font-size: 0.6rem">
                @if (!empty($creditName))
                    {{ now()->format('Y') }} {{ $creditName }}. All rights reserved.
                @else
                    {{ $credit['rights'] }}
                @endif
            </p>
            <p class="text-sm text-muted" style="font-size: 0.6rem">
                {{ $creditName ?? $credit['name'] }}
                <br>
                {{ $creditAddress ?? $credit['address'] }}
            </p>
        </div>
    </div>
</div>
