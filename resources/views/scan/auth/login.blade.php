<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Scan</title>

    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="black">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Include Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    {{-- bootstrap --}}
    <link href="{{ asset('offlines/offline-css/5.3.0-bootstrap.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @vite([
        'resources/js/scan.js'
    ])
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5 mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Đăng nhập</h3>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <form method="POST" id="loginForm" action="{{ route('scan.login.post') }}">
                            @csrf
                            @include('components.form-groups.input-group', [
                                'id'                => "username",
                                'model'             => null,
                                'type'              => "text",
                                'label'             => "Username",
                                'formClass'         => 'form-group mb-3',
                                'placeholder'       => "Username",
                                'required'          => true,
                                'autofocus'         => true,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "password",
                                'model'             => null,
                                'type'              => "password",
                                'label'             => __('users.attributes.password'),
                                'formClass'         => 'mb-3',
                                'inputClass'        => 'form-control text-sm',
                                'placeholder'       => __('users.attributes.password'),
                                'required'          => true,
                            ])
                            <div class="d-grid text-center">
                                <button type="submit" id="submitBtn" class="btn btn-primary">Đăng nhập</button>
                            </div>
                        </form>
                        <div class="d-grid text-center">
                            <a id="btn-open-camera" href="" class="text-sm mt-2">
                                <i class="fa fa-qrcode"></i>
                                Quét Qrcode để đăng nhập
                            </a>
                        </div>
                        <div id="cameraBtns" class="w-100 p-2 bg-white rounded" style="
                            display: none;
                            /* position: absolute;
                            top: 3rem;
                            z-index: 99;
                            left: 0; */
                            "
                        >
                            <div id="camera-qrcode-reader"
                                data-url="{{ route('scan.login-by-qrcode') }}"
                                {{-- data-url="{{ route('scan.login.post') }}" --}}
                                data-placeholder="{{ asset('assets/images/placeholders/camera.png') }}"
                            ></div>
                            <div id="camera-placeholder" class="p-4">
                                <img src="{{ asset('assets/images/placeholders/camera.png') }}" alt="" width="100%" loading="lazy">
                            </div>
                            <div class="text-center mt-3">
                                <div class="fst-italic text-sm">Đưa camera về phía mã Qrcode để quét</div>
                                <a href="#" class="btn btn-sm btn-primary" id="cameraBtn" title="Mở camera" style="display: none;">
                                    <x-icon name="camera"/>
                                    Mở camera
                                </a>
                                <a href="#" class="btn btn-sm btn-danger" id="stopBtn" style="">
                                    <x-icon name="back"/>
                                    Thoát
                                </a>
                            </div>
                            <audio id="sound_success" src="{{ asset("assets/sounds/success.wav") }}"></audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @vite([
        'resources/js/scan/cameraLoginByQrcode.js'
    ])
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="{{ asset('offlines/offline-js/jquery-3.7.1.min.js') }}"></script>
    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submitBtn').prop('disabled', true).text('Logging in...');
            $.ajax({
                url: $('#loginForm').attr('action'),
                type: 'POST',
                data: $('#loginForm').serialize(),
                success: function(res) {
                    window.location.href = res.redirect_url || '{{ route('scan.index') }}';
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Login failed.');
                    $btn.prop('disabled', false).text('Login');
                }
            });
        });
    </script>

    <!-- Include Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
