<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('assets/images/brand/favicon.png') }}" rel="icon" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle.' | '.config('app.name') : config('app.name') }}</title>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>

    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js'
    ])

    @if (!empty($errors) && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach ($errors->all() as $error)
                    toastr.error(@json($error));
                @endforeach
            });
        </script>
    @endif

    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                toastr.success(@json(session('status')));
            });
        </script>
    @endif

    @stack('css')
</head>
<body class="auth-body">

<div class="auth-shell">

    {{-- ====== LEFT PANEL ====== --}}
    <div class="auth-panel auth-panel--left">

        <a href="{{ route('home') }}" class="auth-panel__logo">
            <img src="{{ asset('assets/images/logo-transparent.png') }}"
                 alt="Giltech Solutions"
                 class="auth-panel__logo-icon">
            <span class="auth-panel__logo-text">GIL-TECH <em>Solutions</em></span>
        </a>

        <div class="auth-panel__hero">
            <div class="auth-panel__device">
                <img src="{{ asset('assets/images/backgrounds/checkin-login.png') }}"
                     alt="Giltech checkin dashboard"
                     class="auth-panel__device-img">
            </div>
        </div>

        <div class="auth-panel__copy">
            <h2 class="auth-panel__headline">Quản lý sự kiện<br>chuyên nghiệp</h2>
            <p class="auth-panel__sub">Nền tảng check-in và vận hành sự kiện toàn diện, dễ tuỳ chỉnh</p>
            <div class="auth-panel__dots">
                <span class="auth-dot auth-dot--active"></span>
                <span class="auth-dot"></span>
                <span class="auth-dot"></span>
            </div>
        </div>

    </div>

    {{-- ====== RIGHT PANEL ====== --}}
    <div class="auth-panel auth-panel--right">
        <div class="auth-form-wrap">
            @yield('auth-content')
        </div>
    </div>

</div>

@stack('js')
</body>
</html>
