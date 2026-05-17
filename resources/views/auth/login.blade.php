@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title">Chào mừng trở lại!</h1>
    <p class="auth-form__sub">Vui lòng nhập thông tin tài khoản để tiếp tục</p>

    <form id="loginForm" action="{{ route('login') }}" method="POST" novalidate>
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Địa chỉ email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="auth-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="you@company.com"
                autocomplete="email"
                autofocus
                required
            >
            @error('email')
                <div class="invalid-feedback d-block mt-1" style="font-size:.8rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Mật khẩu</label>
            <input
                type="password"
                id="password"
                name="password"
                class="auth-input @error('password') is-invalid @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >
            @error('password')
                <div class="invalid-feedback d-block mt-1" style="font-size:.8rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3" style="font-size:.85rem;">
            <label class="d-flex align-items-center gap-2 text-secondary" style="cursor:pointer;">
                <input type="checkbox" name="remember" @checked(old('remember'))>
                @lang('auth.remember_me')
            </label>
            <a href="{{ route('password.request') }}" style="color:#0369a1;font-weight:600;text-decoration:none;">
                @lang('auth.forgotten_password')
            </a>
        </div>

        <button type="submit" id="submitBtn" class="auth-btn-primary">
            @lang('auth.login')
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>

    <div class="auth-footer">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay</a>
    </div>

@endsection

@push('js')
<script>
$('#loginForm').on('submit', function(e) {
    e.preventDefault();
    const $btn = $('#submitBtn').prop('disabled', true);
    $btn.html('Đang đăng nhập… <i class="fa-solid fa-spinner fa-spin"></i>');
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            window.location.href = res.redirect_url || '/admin/dashboard';
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Đăng nhập thất bại.');
            $btn.prop('disabled', false).html('@lang('auth.login') <i class="fa-solid fa-arrow-right"></i>');
        }
    });
});
</script>
@endpush
