@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title">Đặt lại mật khẩu</h1>
    <p class="auth-form__sub">Nhập mật khẩu mới cho tài khoản của bạn</p>

    <form action="{{ route('password.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label class="auth-label" for="email">Địa chỉ email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="auth-input @error('email') is-invalid @enderror"
                value="{{ $email ?? old('email') }}"
                placeholder="you@company.com"
                autocomplete="email"
                required
            >
            @error('email')
                <div class="invalid-feedback d-block mt-1" style="font-size:.8rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Mật khẩu mới</label>
            <input
                type="password"
                id="password"
                name="password"
                class="auth-input @error('password') is-invalid @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
                required
            >
            @error('password')
                <div class="invalid-feedback d-block mt-1" style="font-size:.8rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Xác nhận mật khẩu</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="auth-input"
                placeholder="••••••••"
                autocomplete="new-password"
                required
            >
        </div>

        <button type="submit" class="auth-btn-primary">
            @lang('auth.reset_password')
            <i class="fa-solid fa-check"></i>
        </button>
    </form>

    <div class="auth-footer mt-3">
        <a href="{{ route('login') }}" style="color:#0369a1;font-weight:600;text-decoration:none;">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại đăng nhập
        </a>
    </div>

@endsection
