<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('assets') }}/images/brand/favicon.png" rel="icon" type="image/png">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- PWA Support -->
    {{-- <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="black">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black"> --}}

    {{-- GOOGLE RECAPTCHA --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {{-- kết nối với máy chủ google, để xác thực, không nên dùng offline --}}

    <!-- jQuery (Must Load First) -->
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>

    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js'
    ])

    @include('components.metadata', [
        'title'         => config("metapage.title"),
        'description'   => config("metapage.description"),
        'robots'        => config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => config("metapage.image"),
        'language'      => app()->getLocale(),
    ])

    {{-- Toast --}}
    @if (!empty($errors) && $errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        </script>
    @endif

    @if (session('success'))
        <script>
            toastr.success(@json(session('success')));
        </script>
    @endif

    @stack('css')
    @stack('inline-scripts')

    <!-- Service Worker Registration -->
    {{-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script> --}}
</head>

<body class="web-body d-flex flex-column vh-100" style="
        background: url('{{ asset('assets/images/backgrounds/building.jpg') }}') no-repeat center center;
        background-size: cover;
    "
>
    @include('shared/navbar')

    <div class="container flex-grow-1">
        {{-- @include('shared/alerts') --}}

        <main class="my-2">
            @yield('content')
        </main>
    </div>

    @include('shared/footer', [
        'class' => 'py-2'
    ])

    @stack('js')
</body>
</html>
