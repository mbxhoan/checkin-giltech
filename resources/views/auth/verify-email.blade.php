@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title">Xác minh email</h1>
    <p class="auth-form__sub">
        Vui lòng kiểm tra hộp thư và nhấn vào liên kết xác minh chúng tôi đã gửi đến email của bạn.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success rounded-3 mb-3" style="font-size:.875rem;">
            <i class="fa-solid fa-circle-check me-2"></i>
            Đã gửi lại email xác minh thành công.
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <form action="{{ route('verification.send') }}" method="POST">
            @csrf
            <button type="submit" class="auth-btn-primary">
                Gửi lại email xác minh
                <i class="fa-solid fa-envelope"></i>
            </button>
        </form>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-100 btn btn-light rounded-3" style="font-size:.9rem;padding:.7rem;">
                Đăng xuất
            </button>
        </form>
    </div>

@endsection
