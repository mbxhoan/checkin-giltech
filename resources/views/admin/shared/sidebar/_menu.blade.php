@php
    $xIconPrefix = $menu['x_icon_prefix'] ?? "fa-solid";
@endphp

<li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $menu['text'] ?? __('dashboard.dashboard') }}">
    <a class="nav-link text-sub-menu {{ request()->route()->named($menu['route_prefix']) ? 'active' : '' }}" href="{{ route($menu['route']) }}">
        <i class="{{ "{$xIconPrefix} fa-{$menu['x_icon_name']} fa-fw" }}" aria-hidden="true"></i>
        <span class="nav-link-text">
            {{ $menu['text'] ?? __("dashboard.{$key}") }}
        </span>
    </a>
</li>
