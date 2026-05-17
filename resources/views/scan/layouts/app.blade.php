<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('meta-data')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ !empty($favicon) ? $favicon : (asset('assets')."/images/brand/favicon.png") }}" rel="icon" type="image/png">
    <link rel="preload" as="image" href="{{ $mainBg ?? '' }}">

    <!-- jQuery (Must Load First) -->
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/1.13.6-dataTables.bootstrap5.min.css') }}">
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-dataTables.bootstrap5.min.js') }}"></script>

    <!-- Include Toastr CSS -->
    <link href="{{ asset('offlines/offline-css/toastr.min.css') }}" rel="stylesheet">

    <!-- Include SweetAlert2 CSS -->
    <link href="{{ asset('offlines/offline-css/sweetalert2.min.css') }}" rel="stylesheet">

    {{-- Sortable --}}

    {{-- Boostrap --}}
    <link href="{{ asset('offlines/offline-js/5.3.5-bootstrap.min.js') }}" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <title>{{ !empty($pageTitle) ? $pageTitle." | ".config('app.name') : config('app.name', 'Laravel') }}</title>

    <!-- PWA Support -->
    {{-- <link rel="manifest" href="/manifest.json"> --}}
    <link rel="manifest" href="{{ asset('/manifest.webmanifest') }}">
    <meta name="theme-color" content="black">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    @vite([
        'resources/sass/app.scss',
        'resources/sass/scan.scss',
        'resources/js/app.js',
        'resources/js/scan.js'
    ])

    @include('components.metadata', [
        'title'         => config("metapage.title"),
        'description'   => config("metapage.description"),
        'robots'        => config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => config("metapage.image"),
        'language'      => app()->getLocale(),
    ])

    {{-- @toastr_css --}}

    @stack('scan_css')

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

    @livewireStyles
</head>
<body class="{{ $class ?? 'scan-body' }}" style="
        background-color: #dddddd78; /* adjust to match image tone */
        background-image: url('{{ $mainBg ?? null }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-attachment: fixed;
        background-size: cover;
    "
>
    <div class="main-content">
        @yield('content')
    </div>

    <!-- DataTables JS -->
    <script type="text/javascript" src="{{ asset('offlines/offline-js/dt-1.12.1-r-2.3.0-datatables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

    <!-- Include Toastr JS -->
    <script src="{{ asset('offlines/offline-js/toastr.min.js') }}"></script>

    {{-- ChartJs --}}
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>

    <!-- Include SweetAlert2 JS -->
    <script src="{{ asset('offlines/offline-js/sweetalert2@11.js') }}"></script>

    {{-- Sortable --}}

    {{-- Bootstrap --}}
    <script src="{{ asset('offlines/offline-js/2.11.8-popper.min.js') }}" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="{{ asset('offlines/offline-js/5.3.5-bootstrap.min.js') }}" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
    <script src="{{ asset('offlines/offline-js/5.3.5-bootstrap.bundle.min.js') }}" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

    {{-- Toast --}}
    {{-- @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        </script>
    @endif --}}

    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @endif

    @if (session('success'))
        <script>
            toastr.success(@json(session('success')));
        </script>
    @endif

    @stack('scan_js')

    @livewireScripts
</body>

{{-- @toastr_js --}}
{{-- @toastr_render --}}
</html>
