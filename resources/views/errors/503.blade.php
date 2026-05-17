@extends('layouts.app')

@section('title', '503 Service Unavailable')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-lg-6">
            <div class="surface-card p-5 text-center">
                <x-brand-lockup href="{{ route('home') }}" theme="light" class="justify-content-center mb-4" />
                <span class="text-uppercase text-primary fw-bold small">503</span>
                <h1 class="mt-2 mb-3">Dịch vụ tạm thời gián đoạn</h1>
                <p class="text-secondary mb-4">
                    Giltech Solutions đang bảo trì hoặc xử lý tải cao. Bạn có thể thử lại sau trong ít phút.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary">Trang chủ</a>
                    <a href="{{ url()->previous() }}" class="btn btn-light">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
@endsection
