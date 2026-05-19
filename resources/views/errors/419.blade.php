@extends('errors.layout')

@section('title', '419 Phiên hết hạn')

@section('content')
    <span class="badge">419</span>
    <h1>Phiên làm việc đã hết hạn</h1>
    <p>Phiên thao tác của bạn không còn hiệu lực. Hãy tải lại trang hoặc đăng nhập lại để tiếp tục.</p>
    <div class="actions">
        <a href="javascript:location.reload()" class="btn btn-primary">Tải lại</a>
        <a href="/login" class="btn btn-light">Đăng nhập</a>
    </div>
@endsection
