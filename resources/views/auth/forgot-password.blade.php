@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title">Quên mật khẩu?</h1>
    <p class="auth-form__sub">Nhập email tài khoản — chúng tôi sẽ gửi link đặt lại mật khẩu ngay</p>

    @if (session('status'))
        <div class="alert alert-success rounded-3 mb-3" style="font-size:.875rem;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" novalidate>
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

        <button type="submit" class="auth-btn-primary">
            @lang('auth.send_password_reset_link')
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>

    <div class="auth-footer mt-3">
        <a href="{{ route('login') }}" style="color:#0369a1;font-weight:600;text-decoration:none;">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại đăng nhập
        </a>
    </div>

@endsection
