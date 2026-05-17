@props([
    'href' => null,
    'theme' => 'dark',
    'pill' => null,
])

@php
    $href = $href ?: route('home');
@endphp

<a href="{{ $href }}" {{ $attributes->class(["brand-lockup", "brand-lockup--{$theme}"]) }}>
    <span class="brand-lockup__mark">
        <img
            src="{{ asset('assets/images/brand/favicon.png') }}"
            alt="Giltech Solutions"
            width="18"
            height="18"
            loading="eager"
        >
    </span>
    <span class="brand-lockup__text">
        <span class="brand-lockup__title">Giltech Solutions</span>
        @if (!empty($pill))
            <span class="brand-lockup__pill">{{ $pill }}</span>
        @endif
    </span>
</a>
