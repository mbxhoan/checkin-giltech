@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title">Đang cập nhật</h1>
    <p class="auth-form__sub">
        Tính năng đăng ký tài khoản đang được hoàn thiện.<br>
        Vui lòng liên hệ quản trị viên để được cấp tài khoản.
    </p>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="auth-btn-primary d-inline-flex" style="width:auto;padding:.7rem 1.75rem;text-decoration:none;">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại đăng nhập
        </a>
    </div>

    <div class="auth-footer mt-4">
        Liên hệ hỗ trợ:
        <a href="mailto:{{ config('info.admin_email') }}">{{ config('info.admin_email') }}</a>
    </div>

@endsection
