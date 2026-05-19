@extends('errors.layout')

@section('title', '500 Lỗi máy chủ')

@section('content')
    <span class="badge">500</span>
    <h1>Hệ thống đang gặp sự cố</h1>
    <p>Yêu cầu đã được ghi nhận nhưng máy chủ chưa thể xử lý ngay lúc này. Vui lòng thử lại sau ít phút.</p>
    <div class="actions">
        <a href="javascript:history.back()" class="btn btn-primary">Quay lại</a>
        <a href="/" class="btn btn-light">Trang chủ</a>
    </div>
@endsection
