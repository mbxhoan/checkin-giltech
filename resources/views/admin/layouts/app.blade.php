<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets') }}/images/brand/favicon.png" rel="icon" type="image/png">

    <!-- Select 2 -->
    <link href="{{ asset('offlines/offline-css/4.1.0-select2.min.css') }}" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/1.13.6-dataTables.bootstrap5.min.css') }}">

    <!-- Include Toastr CSS -->
    <link href="{{ asset('offlines/offline-css/toastr.min.css') }}" rel="stylesheet">

    <!-- Include SweetAlert2 CSS -->
    <link href="{{ asset('offlines/offline-css/sweetalert2.min.css') }}" rel="stylesheet">

    {{-- GOOGLE RECAPTCHA --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    {{-- Sortable --}}

    {{-- Boostrap --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script> --}}
    <link href="{{ asset('offlines/offline-css/5.3.5-bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <!-- ckeditor -->
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/45.0.0-ckeditor5.css') }}" crossorigin>
    <link rel="stylesheet" href="{{ asset('offlines/offline-css/45.0.0-ckeditor5-premium-features.css') }}" crossorigin>

    <title>{{ !empty($pageTitle) ? $pageTitle." | ".config('app.name') : config('app.name', 'Laravel') }}</title>

    @include('components.metadata', [
        'title'         => !empty($pageTitle) ? $pageTitle : '',
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
                    @admin
                        @include('shared/alerts')
                    @endadmin

                    <x-card>
                        @yield('content')
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (Must Load First) -->
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>

    <!-- Select 2 -->
    <script src="{{ asset('offlines/offline-js/4.1.0-select2.min.js') }}"></script>

    <!-- DataTables JS -->
     <script type="text/javascript" src="{{ asset('offlines/offline-js/2.3.0-dataTables.min.js') }}"></script>
     {{-- <script type="text/javascript" src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script> --}}
    {{-- <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.12.1/r-2.3.0/datatables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

    <!-- Include Toastr JS -->
    <script src="{{ asset('offlines/offline-js/toastr.min.js') }}"></script>

    {{-- ChartJs --}}
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>

    <!-- Include SweetAlert2 JS -->
    <script src="{{ asset('offlines/offline-js/sweetalert2@11.js') }}"></script>

    {{-- Sortable --}}

    {{-- Bootstrap --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.min.js" integrity="sha384-RuyvpeZCxMJCqVUGFI0Do1mQrods/hhxYlcVfGPOfQtPJh0JCw12tUAZ/Mv10S7D" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script> --}}

    <!-- ckeditor -->
    <script src="{{ asset('offlines/offline-js/45.0.0-ckeditor5.umd.js') }}" crossorigin></script>
    <script src="{{ asset('offlines/offline-js/45.0.0-ckeditor5-premium-features.umd.js') }}" crossorigin></script>
    <script src="{{ asset('offlines/offline-js/45.0.0-vi.umd.js') }}" crossorigin></script>
    <script src="{{ asset('offlines/offline-js/45.0.0-premium-vi.umd.js') }}" crossorigin></script>
    <script src="{{ asset('offlines/offline-js/2.6.1-ckbox.js') }}" crossorigin></script>

    {{-- Toast --}}
    {{-- @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        </script>
    @endif --}}

    @admin
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
    @endadmin

    @stack('admin_js')

    @livewireScripts
</body>
</html>
