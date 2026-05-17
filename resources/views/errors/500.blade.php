@extends('layouts.app')

@section('title', '500 Server Error')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-lg-6">
            <div class="surface-card p-5 text-center">
                <x-brand-lockup href="{{ route('home') }}" theme="light" class="justify-content-center mb-4" />
                <span class="text-uppercase text-primary fw-bold small">500</span>
                <h1 class="mt-2 mb-3">Hệ thống đang gặp sự cố</h1>
                <p class="text-secondary mb-4">
                    Yêu cầu đã được ghi nhận nhưng máy chủ chưa thể xử lý ngay lúc này. Vui lòng thử lại sau ít phút.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Quay lại</a>
                    <a href="{{ route('home') }}" class="btn btn-light">Trang chủ</a>
                </div>
            </div>
        </div>
    </div>
@endsection
