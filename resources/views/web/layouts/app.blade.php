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
    <link rel="stylesheet" href="offlines/offline-css/1.13.6-dataTables.bootstrap5.min.css">
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-dataTables.bootstrap5.min.js') }}"></script>

    <!-- Include Toastr CSS -->
    <link href="{{ asset('offlines/offline-css/toastr.min.css') }}" rel="stylesheet">

    <!-- Include SweetAlert2 CSS -->
    <link href="{{ asset('offlines/offline-css/sweetalert2.min.css') }}" rel="stylesheet">

    {{-- Sortable --}}

    {{-- Boostrap --}}
    <link href="{{ asset('offlines/offline-css/5.3.5-bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <title>{{ !empty($pageTitle) ? $pageTitle." | ".config('app.name') : config('app.name', 'Laravel') }}</title>

    @vite([
        'resources/sass/app.scss',
        'resources/sass/web.scss',
        'resources/js/app.js',
        'resources/js/web.js'
    ])

    {{-- @toastr_css --}}

    @stack('web_css')
    @livewireStyles
</head>

{{-- background-color: #1a1a1a; /* adjust to match image tone */
background:url('{{ $mainBg ?? null }}')
no-repeat
center center
fixed;
background-size: cover; --}}

<body class="web-body" style="
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
    @if ($errors->any())
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

    @stack('js')

    @livewireScripts
</body>

{{-- @toastr_js --}}
{{-- @toastr_render --}}
</html>
