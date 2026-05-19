@extends('errors.layout')

@section('title', '404 Không tìm thấy')

@section('content')
    <span class="badge">404</span>
    <h1>Không tìm thấy trang</h1>
    <p>Liên kết này có thể đã thay đổi, bị xóa hoặc không còn khả dụng trong hệ thống Giltech Solutions.</p>
    <div class="actions">
        <a href="javascript:history.back()" class="btn btn-primary">Quay lại</a>
        <a href="/" class="btn btn-light">Trang chủ</a>
    </div>
@endsection
