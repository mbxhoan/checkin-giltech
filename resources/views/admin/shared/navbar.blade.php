@php
    $user = Auth::user();
    $roleLabel = $user->isSysAdmin()
        ? 'Sys Admin'
        : ($user->isAdmin() ? 'Admin' : 'Operator');
@endphp

<header class="admin-topbar">
    <div class="admin-topbar__mobile-brand">
        <button
            class="admin-topbar__toggle"
            id="adminSidebarToggle"
            type="button"
            aria-label="Mở menu điều hướng"
            aria-controls="adminSidebar"
            aria-expanded="false"
        >
            <x-icon name="bars" />
        </button>

        <x-brand-lockup
            href="{{ route('admin.dashboard') }}"
            theme="dark"
            pill="Portal"
            class="d-lg-none"
        />
    </div>

    <div class="admin-topbar__search">
        <label class="admin-searchbox" for="adminNavSearch">
            <x-icon name="magnifying-glass" />
            <input
                id="adminNavSearch"
                type="search"
                autocomplete="off"
                placeholder="Tìm nhanh module, báo cáo, người dùng..."
            >
            <span class="admin-searchbox__kbd">/</span>
        </label>
        <div class="admin-nav-search-results d-none" id="adminNavSearchResults"></div>
    </div>

    <div class="admin-topbar__actions">
        <div class="admin-user-chip">
            <span class="admin-user-chip__meta">{{ $roleLabel }}</span>
            <strong>{{ $user->name }}</strong>
        </div>

        <div class="dropdown">
            <button
                class="admin-topbar__profile dropdown-toggle"
                id="navbarDropdownMenuLink"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                type="button"
            >
                <span>{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            </button>

            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="navbarDropdownMenuLink">
                <a href="{{ route('users.edit') }}" class="dropdown-item">
                    @lang('users.settings')
                </a>

                <a href="{{ asset(config("info.document.internal_path")) }}" class="dropdown-item" download>
                    Tài liệu hướng dẫn
                </a>

                @admin
                    <a href="{{ route('admin.histories.index') }}" class="dropdown-item">
                        Lịch sử thao tác
                    </a>
                @endadmin

                @sys_admin
                    <a href="{{ route('admin.logs') }}" target="_blank" class="dropdown-item">
                        Activity Logs
                    </a>
                    <a href="{{ route('admin.api-client-logs.index') }}" class="dropdown-item">
                        API Logs
                    </a>
                @endsys_admin

                <div class="dropdown-divider"></div>

                <a href="{{ url('/logout') }}"
                    class="dropdown-item"
                    data-loading-nav="Đang đăng xuất..."
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    @lang('auth.logout')
                </a>
                <form id="logout-form" class="d-none" action="{{ url('/logout') }}" method="POST">
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </div>
</header>
