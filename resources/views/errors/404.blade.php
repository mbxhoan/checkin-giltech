@extends('layouts.app')

@section('title', '404 Not Found')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-lg-6">
            <div class="surface-card p-5 text-center">
                <x-brand-lockup href="{{ route('home') }}" theme="light" class="justify-content-center mb-4" />
                <span class="text-uppercase text-primary fw-bold small">404</span>
                <h1 class="mt-2 mb-3">Không tìm thấy trang</h1>
                <p class="text-secondary mb-4">
                    Liên kết này có thể đã thay đổi, bị xóa hoặc không còn khả dụng trong hệ thống Giltech Solutions.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Quay lại</a>
                    <a href="{{ route('home') }}" class="btn btn-light">Trang chủ</a>
                </div>
            </div>
        </div>
    </div>
@endsection
