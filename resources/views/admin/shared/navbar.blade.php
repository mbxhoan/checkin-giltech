<nav class="navbar bg-dark fixed-top navbar-expand-lg" data-bs-theme="dark">
    <div class="container">
        <!-- Branding Image -->
        <a href="{{ route('home') }}" class="navbar-brand pb-0">
            {{-- {{ config('app.name', 'Laravel') }} --}}
            <img src="{{ asset(config('info.page.logo_1.internal_path_white')) }}" alt="{{  __('dashboard.dashboard') }}" height="auto" width="25%">
        </a>

        <!-- Collapsed Hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            @include('admin/shared/sidebar/sidebar')

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        {{-- <a href="{{ route('users.show', Auth::user()) }}" class="dropdown-item">
                            @lang('users.public_profile')
                        </a> --}}

                        <a href="{{ route('users.edit') }}" class="dropdown-item">
                            @lang('users.settings')
                        </a>

                        <a href="{{ asset(config("info.document.internal_path")) }}" class="dropdown-item" download>
                            Tài liệu - HDSD
                        </a>

                        @admin
                            <a href="{{ route('admin.histories.index') }}" class="dropdown-item">
                                Lịch sử
                            </a>
                        @endadmin

                        @sys_admin
                            {{-- <a href="{{ route('admin.logs') }}" target="_blank" class="dropdown-item">
                                Telescope
                            </a> --}}
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
                            onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                            @lang('auth.logout')
                        </a>
                        <form id="logout-form" class="d-none" action="{{ url('/logout') }}" method="POST">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
