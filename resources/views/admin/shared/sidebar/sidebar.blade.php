@php
    $sidebarMenus = config("sidebar-menu.admin");
    $user = Auth::user();
    $exceptMenus = [];
    $filteredMenus = [];

    if (!auth()->user()->isAdmin()) {
        unset($sidebarMenus['users']);
    }

    if (!auth()->user()->isSysAdmin()) {
        unset($sidebarMenus['media']);
        if ($user->package_id) {
            $exceptMenus = config("info.packages.{$user->package->code}.excepts.menus") ?? [];

            /* Chùa Minh Hiệp */
            if ($user->email == "cmh01@gmail.com") {
                $exceptMenus = array_filter($exceptMenus, function ($item) {
                    return !in_array($item, [
                        'landing_pages',
                        'campaigns',
                    ]);
                });

                $exceptMenus = array_values($exceptMenus);
            }

            /* Thành - MKT */
            if ($user->email == "thanh.nv@delfi.com.vn") {
                $exceptMenus = array_filter($exceptMenus, function ($item) {
                    return !in_array($item, [
                        'campaigns',
                    ]);
                });

                $exceptMenus = array_values($exceptMenus);
            }
        }
    }

    foreach ($sidebarMenus as $key => $menu) {
        $originalMenu = $menu;
        $hasVisibleSubMenus = false;

        if (isset($menu['subMenus']) && is_array($menu['subMenus'])) {
            foreach ($menu['subMenus'] as $subKey => $subMenu) {
                /* remove by excepts */
                if (count($exceptMenus) && in_array($subKey, $exceptMenus)) {
                    unset($menu['subMenus'][$subKey]);
                }

                $isVisible = false;

                if ($user->is_admin && isset($subMenu['is_admin']) && $subMenu['is_admin']) {
                    $isVisible = true;
                }

                if (
                    isset($subMenu['roles']) &&
                    is_array($subMenu['roles']) &&
                    count($subMenu['roles']) > 0
                ) {
                    foreach ($subMenu['roles'] as $role) {
                        if ($user->hasRole($role)) {
                            $isVisible = true;
                            break;
                        }
                    }
                }

                if (!$isVisible) {
                    unset($menu['subMenus'][$subKey]);
                } else {
                    $hasVisibleSubMenus = true;
                }
            }

            /* remove menu if no subMenus left */
            if (!count($menu['subMenus'])) {
                continue;
            }
        }

        $allowMenu = false;

        if ($hasVisibleSubMenus) {
            $allowMenu = true;
        }

        if (!$allowMenu && $user->is_admin && isset($menu['is_admin']) && $menu['is_admin']) {
            $allowMenu = true;
        }

        if (!$allowMenu && isset($menu['roles']) && is_array($menu['roles']) && count($menu['roles']) > 0) {
            foreach ($menu['roles'] as $role) {
                if ($user->hasRole($role)) {
                    $allowMenu = true;
                    break;
                }
            }
        }

        if ($allowMenu) {
            $filteredMenus[$key] = $menu;
        }
    }

    $sidebarMenus = $filteredMenus;
@endphp

<ul class="navbar-nav navbar-sidenav hide-scrollbar">
    @foreach ($sidebarMenus as $key => $menu)
        @if (isset($menu['subMenus']))
            <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $menu['text'] ?? __("dashboard.dashboard") }}">
                <span class="nav-link text-sub-menu-title fw-bold {{ request()->route()->named($menu['route_prefix']) ? 'active' : '' }}">
                    <span class="nav-link-text text-lg">
                        {{ $menu['text'] ?? __("dashboard.{$key}") }}
                    </span>
                </span>
            </li>
            @foreach ($menu['subMenus'] as $subMenuKey => $subMenu)
                @include('admin.shared.sidebar._menu', [
                    'key'   => $subMenuKey,
                    'menu'  => $subMenu,
                ])
            @endforeach
        @else
            @include('admin.shared.sidebar._menu', [
                'key'   => $key,
                'menu'  => $menu,
            ])
        @endif
    @endforeach
</ul>

<ul class="navbar-nav sidenav-toggler">
    <li class="nav-item">
        <a class="nav-link text-center" id="sidenavToggler">
            <x-icon name="angle-left" />
        </a>
    </li>
</ul>
