@php
    $sidebarMenus = config("sidebar-menu.admin");
    $user = Auth::user();

    if (!auth()->user()->isAdmin()) {
        unset($sidebarMenus['users']);
        unset($sidebarMenus['media']);
    }

    $sidebarMenus = array_filter($sidebarMenus, function ($menu) use ($user) {
        if (isset($menu['subMenus'])) {
            foreach ($menu['subMenus'] as $key => $subMenu) {
                $stillGood = false;

                if ($user->is_admin) {
                    if (isset($subMenu['is_admin']) && $subMenu['is_admin']) {
                        return true;
                    }
                }

                if (isset($subMenu['roles']) && (is_array($subMenu['roles']) && count($subMenu['roles']))) {
                    foreach ($subMenu['roles'] as $role) {
                        if ($user->hasRole($role)) {
                            $stillGood = true;
                        }
                    }
                }

                if (!$stillGood) {
                    unset($menu['subMenus'][$key]);
                }
            }
        }

        if ($user->is_admin) {
            if (isset($menu['is_admin']) && $menu['is_admin']) {
                return true;
            }
        }

        if (isset($menu['roles']) && (is_array($menu['roles']) && count($menu['roles']))) {
            $stillGood = false;

            foreach ($menu['roles'] as $role) {
                $stillGood = true;
            }

            return $stillGood;
        }

        return false;
    });
@endphp

<ul class="navbar-nav navbar-sidenav">
    @foreach ($sidebarMenus as $key => $menu)
        @if (isset($menu['subMenus']))
            <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $menu['text'] ?? __("dashboard.dashboard") }}">
                <span class="nav-link text-sub-menu-title fw-bold">
                    <span class="nav-link-text">
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
