@extends('layouts.app')

@section('title', '419 Page Expired')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-lg-6">
            <div class="surface-card p-5 text-center">
                <x-brand-lockup href="{{ route('home') }}" theme="light" class="justify-content-center mb-4" />
                <span class="text-uppercase text-primary fw-bold small">419</span>
                <h1 class="mt-2 mb-3">Phiên làm việc đã hết hạn</h1>
                <p class="text-secondary mb-4">
                    Phiên thao tác của bạn không còn hiệu lực. Hãy tải lại trang hoặc đăng nhập lại để tiếp tục.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ url()->current() }}" class="btn btn-primary">Tải lại</a>
                    <a href="{{ route('login') }}" class="btn btn-light">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
@endsection
