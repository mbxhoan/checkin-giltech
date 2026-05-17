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
            if ($user->email == "thanh.nv@giltech.com.vn") {
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

<aside class="admin-sidebar hide-scrollbar" id="adminSidebar">
    <div class="admin-sidebar__header">
        <x-brand-lockup
            href="{{ route('admin.dashboard') }}"
            theme="light"
            pill="Admin"
            class="admin-sidebar__brand"
        />

        <button class="admin-sidebar__close d-lg-none" id="adminSidebarClose" type="button" aria-label="Đóng menu">
            <x-icon name="xmark" />
        </button>
    </div>

    <div class="admin-sidebar__body">
        @if ($user->isAdmin())
            <div class="admin-sidebar__section">
                <span class="admin-sidebar__eyebrow">Thao tác nhanh</span>
                <a
                    href="{{ route('admin.events.create') }}"
                    class="admin-sidebar__quick-link"
                    data-loading-nav="Đang mở form tạo sự kiện..."
                >
                    <x-icon name="plus" />
                    <span>Tạo sự kiện mới</span>
                </a>
            </div>
        @endif

        <div class="admin-sidebar__section">
            <span class="admin-sidebar__eyebrow">Điều hướng</span>
            <ul class="admin-sidebar__menu">
                @foreach ($sidebarMenus as $key => $menu)
                    @if (isset($menu['subMenus']))
                        <li class="admin-sidebar__group">
                            <span class="admin-sidebar__group-title {{ request()->route()->named($menu['route_prefix']) ? 'is-active' : '' }}">
                                {{ $menu['text'] ?? __("dashboard.{$key}") }}
                            </span>
                            <ul class="admin-sidebar__submenu">
                                @foreach ($menu['subMenus'] as $subMenuKey => $subMenu)
                                    @include('admin.shared.sidebar._menu', [
                                        'key' => $subMenuKey,
                                        'menu' => $subMenu,
                                    ])
                                @endforeach
                            </ul>
                        </li>
                    @else
                        @include('admin.shared.sidebar._menu', [
                            'key' => $key,
                            'menu' => $menu,
                        ])
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="admin-sidebar__footer">
        <div class="admin-sidebar__profile">
            <span class="admin-sidebar__profile-icon">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div>
                <strong>{{ $user->name }}</strong>
                <div>{{ $user->email }}</div>
            </div>
        </div>
    </div>
</aside>
