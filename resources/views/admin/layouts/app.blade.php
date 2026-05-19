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
<body class="admin-body">
    <div class="admin-shell" id="adminShell">
        @include('admin.shared.sidebar.sidebar')

        <div class="admin-shell__main">
            @include('admin.shared.navbar')

            <div class="content-wrapper">
                <div class="container-fluid admin-content-container">
                    @admin
                        @include('shared/alerts')
                    @endadmin

                    <x-card class="admin-content-surface">
                        @yield('content')
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

    <div class="admin-loading-overlay" id="adminLoadingOverlay" aria-hidden="true">
        <div class="admin-loading-overlay__card" role="status" aria-live="polite">
            <span class="admin-loading-overlay__spinner"></span>
            <div class="admin-loading-overlay__copy">
                <strong>Đang xử lý</strong>
                <span id="adminLoadingMessage">Vui lòng chờ trong giây lát...</span>
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

    <!-- Bootstrap Bundle (offline, global - phải load sau jQuery để modal/dismiss hoạt động đúng) -->
    <script src="{{ asset('offlines/offline-js/5.3.5-bootstrap.bundle.min.js') }}"></script>

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

    <script>
        window.GiltechLoadingOverlay = {
            element: null,
            messageElement: null,
            show(message) {
                this.element ??= document.getElementById('adminLoadingOverlay');
                this.messageElement ??= document.getElementById('adminLoadingMessage');

                if (!this.element) {
                    return;
                }

                if (this.messageElement && message) {
                    this.messageElement.textContent = message;
                }

                this.element.classList.add('is-visible');
                this.element.setAttribute('aria-hidden', 'false');
            },
            hide() {
                this.element ??= document.getElementById('adminLoadingOverlay');

                if (!this.element) {
                    return;
                }

                this.element.classList.remove('is-visible');
                this.element.setAttribute('aria-hidden', 'true');
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;

            if ($.fn.dataTable) {
                $.fn.dataTable.ext.errMode = 'none';

                $.extend(true, $.fn.dataTable.defaults, {
                    autoWidth: false,
                    deferRender: true,
                    searchDelay: 350,
                    responsive: true,
                    language: {
                        processing: 'Đang tải dữ liệu...'
                    }
                });

                $(document).on('preXhr.dt', function (_, settings) {
                    settings.nTableWrapper?.classList.add('is-loading');
                });

                $(document).on('draw.dt xhr.dt error.dt', function (_, settings) {
                    settings.nTableWrapper?.classList.remove('is-loading');
                });

                $(document).on('error.dt', function (_, settings, __, message) {
                    settings.nTableWrapper?.classList.remove('is-loading');

                    if (window.toastr) {
                        toastr.error('Không thể tải dữ liệu bảng. Vui lòng thử lại.');
                    }

                    console.error(message);
                });
            }

            window.addEventListener('pageshow', function () {
                window.GiltechLoadingOverlay.hide();
            });
        });
    </script>

    @stack('admin_js')

    @stack('modals')

    @livewireScripts
</body>
</html>
