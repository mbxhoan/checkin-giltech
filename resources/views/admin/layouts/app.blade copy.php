<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets') }}/images/brand/favicon.png" rel="icon" type="image/png">

    <!-- DataTables -->
    <link src="{{ asset('offlines/offline-css/2.3.0-dataTables.bootstrap5.min.css') }}" rel="stylesheet"></link>
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/1.13.6-jquery.dataTables.min.css') }}" />
    {{-- <link src="{{ asset('offlines/offline-css/4.0.1-fixedHeader.dataTables.min.css') }}" rel="stylesheet"></link>
    <link src="{{ asset('offlines/offline-css/2.7.0-autoFill.dataTables.min.css') }}" rel="stylesheet"></link>
    <link src="{{ asset('offlines/offline-js/3.2.3-buttons.colVis.min.js') }}" rel="stylesheet"></link>
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/1.13.6-dataTables.bootstrap5.min.css') }}">
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-dataTables.bootstrap5.min.js') }}"></script> --}}

    <!-- Include Toastr CSS -->
    <link href="{{ asset('offlines/offline-css/toastr.min.css') }}" rel="stylesheet">

    <!-- Include SweetAlert2 CSS -->
    <link href="{{ asset('offlines/offline-css/sweetalert2.min.css') }}" rel="stylesheet">

    {{-- Select2 --}}
    <link href="{{ asset('offlines/offline-css/4.1.0-select2.min.css') }}" rel="stylesheet" />

    {{-- Sortable --}}

    {{-- Boostrap --}}
    <link href="{{ asset('offlines/offline-css/5.3.5-bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <title>{{ !empty($pageTitle) ? $pageTitle." | ".config('app.name') : config('app.name', 'Laravel') }}</title>

    @include('components.metadata', [
        'title'         => config("metapage.title"),
        'description'   => config("metapage.description"),
        'robots'        => config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => config("metapage.image"),
        'language'      => app()->getLocale(),
    ])

    @vite([
        'resources/sass/app.scss',
        'resources/sass/admin.scss',
        'resources/js/app.js',
        'resources/js/admin.js'
    ])

    @stack('admin_css')
    @livewireStyles
</head>
<body class="admin-body bg-dark">
    @include('admin/shared/navbar')

    <div class="content-wrapper bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    @include('shared/alerts')

                    <x-card>
                        @yield('content')
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (Must Load First) -->
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>

    <!-- Include Toastr JS -->
    <script src="{{ asset('offlines/offline-js/toastr.min.js') }}"></script>

    {{-- ChartJs --}}
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>

    <!-- Include SweetAlert2 JS -->
    <script src="{{ asset('offlines/offline-js/sweetalert2@11.js') }}"></script>

    {{-- Select2 --}}
    <script src="{{ asset('offlines/offline-css/4.1.0-select2.min.css') }}"></script>

    {{-- Sortable --}}

    {{-- Bootstrap --}}
    <script src="{{ asset('offlines/offline-js/2.11.8-popper.min.js') }}" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="{{ asset('offlines/offline-js/5.3.5-bootstrap.min.js') }}" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
    <script src="{{ asset('offlines/offline-js/5.3.5-bootstrap.bundle.min.js') }}" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

    <!-- Then DataTables core -->
    {{-- <script type="text/javascript" src="{{ asset('offlines/offline-js/dt-1.12.1-r-2.3.0-datatables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script> --}}

    <!-- DataTables JS -->
    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.3.0-dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('offlines/offline-js/dt-1.12.1-r-2.3.0-datatables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.3.0-dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.3.0-dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('offlines/offline-js/1.13.6-jquery.dataTables.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.3.0-dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.7.0-dataTables.autoFill.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('offlines/offline-js/3.2.3-dataTables.buttons.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('offlines/offline-js/2.0.4-dataTables.colReorder.min.js') }}"></script>

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

    @stack('admin_js')

    @livewireScripts
</body>
</html>
