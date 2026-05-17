@php
    $xIconPrefix = $menu['x_icon_prefix'] ?? "fa-solid";
@endphp

<li class="admin-sidebar__item">
    <a
        class="admin-sidebar__link {{ request()->route()->named($menu['route_prefix']) ? 'is-active' : '' }}"
        href="{{ route($menu['route']) }}"
        data-nav-search-label="{{ strtolower($menu['text'] ?? __("dashboard.{$key}")) }}"
        data-loading-nav="Đang mở {{ strtolower($menu['text'] ?? __("dashboard.{$key}")) }}..."
    >
        <i class="{{ "{$xIconPrefix} fa-{$menu['x_icon_name']} fa-fw" }}" aria-hidden="true"></i>
        <span>
            {{ $menu['text'] ?? __("dashboard.{$key}") }}
        </span>
    </a>
</li>
