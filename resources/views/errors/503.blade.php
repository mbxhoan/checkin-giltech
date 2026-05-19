@extends('errors.layout')

@section('title', '503 Gián đoạn dịch vụ')

@section('content')
    <span class="badge">503</span>
    <h1>Dịch vụ tạm thời gián đoạn</h1>
    <p>Giltech Solutions đang bảo trì hoặc xử lý tải cao. Bạn có thể thử lại sau trong ít phút.</p>
    <div class="actions">
        <a href="/" class="btn btn-primary">Trang chủ</a>
        <a href="javascript:history.back()" class="btn btn-light">Quay lại</a>
    </div>
@endsection
