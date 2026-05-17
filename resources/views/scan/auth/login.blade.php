@extends('scan.layouts.templates.page', [
    'pageTitle' => 'Đăng nhập quét mã',
    'form_width' => 5,
    'form_class' => 'scan-login-shell',
])

@section('meta-data')
    @include('components.metadata', [
        'title' => 'Đăng nhập quét mã',
        'description' => config('metapage.description'),
        'robots' => config('metapage.robots'),
        'url' => url()->current(),
        'image' => config('metapage.image'),
        'language' => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="scan-login">
        <div class="scan-login__intro">
            <p class="scan-login__eyebrow">Giltech Solutions</p>
            <h1 class="scan-login__title">Đăng nhập khu vực check-in</h1>
            <p class="scan-login__description">
                Dùng tài khoản vận hành hoặc quét QR đăng nhập để truy cập nhanh màn hình scan.
            </p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" id="loginForm" action="{{ route('scan.login.post') }}" class="scan-login__form">
            @csrf

            @include('components.form-groups.input-group', [
                'id' => 'username',
                'model' => null,
                'type' => 'text',
                'label' => 'Username',
                'formClass' => 'form-group mb-3',
                'placeholder' => 'Username',
                'required' => true,
                'autofocus' => true,
            ])

            @include('components.form-groups.input-group', [
                'id' => 'password',
                'model' => null,
                'type' => 'password',
                'label' => __('users.attributes.password'),
                'formClass' => 'mb-3',
                'inputClass' => 'form-control text-sm',
                'placeholder' => __('users.attributes.password'),
                'required' => true,
            ])

            <div class="d-grid gap-2">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg btn-submit-form">
                    Đăng nhập
                </button>
                <a id="btn-open-camera" href="#" class="scan-login__camera-link">
                    <x-icon name="qrcode" />
                    Quét QR để đăng nhập nhanh
                </a>
            </div>
        </form>

        <div id="cameraBtns" class="scan-login__camera-panel" style="display: none;">
            <div
                id="camera-qrcode-reader"
                data-url="{{ route('scan.login-by-qrcode') }}"
                data-placeholder="{{ asset('assets/images/placeholders/camera.png') }}"
            ></div>
            <div id="camera-placeholder" class="scan-login__camera-placeholder p-4">
                <img src="{{ asset('assets/images/placeholders/camera.png') }}" alt="" width="100%" loading="lazy">
            </div>
            <div class="text-center mt-3">
                <div class="fst-italic text-sm mb-3">Đưa camera về phía QR code để đăng nhập</div>
                <a href="#" class="btn btn-sm btn-primary" id="cameraBtn" title="Mở camera" style="display: none;">
                    <x-icon name="camera" />
                    Mở camera
                </a>
                <a href="#" class="btn btn-sm btn-outline-danger" id="stopBtn">
                    <x-icon name="circle-xmark" />
                    Đóng camera
                </a>
            </div>
            <audio id="sound_success" src="{{ asset('assets/sounds/success.wav') }}"></audio>
        </div>
    </div>
@endsection

@push('scan_js')
    @vite([
        'resources/js/scan/cameraLoginByQrcode.js'
    ])
    <script src="{{ asset('offlines/offline-js/html5-qrcode.min.js') }}"></script>
    <script>
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            const $btn = $('#submitBtn')
                .prop('disabled', true)
                .text('Đang đăng nhập...');

            $.ajax({
                url: $('#loginForm').attr('action'),
                type: 'POST',
                data: $('#loginForm').serialize(),
                success: function (res) {
                    window.location.href = res.redirect_url || '{{ route('scan.index') }}';
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Đăng nhập thất bại.');
                    $btn.prop('disabled', false).text('Đăng nhập');
                }
            });
        });
    </script>
@endpush
