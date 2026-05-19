@extends('errors.layout')

@section('title', '405 Phương thức không hợp lệ')

@section('content')
    <span class="badge">405</span>
    <h1>Phương thức không được hỗ trợ</h1>
    <p>Yêu cầu của bạn đã tới hệ thống, nhưng thao tác HTTP này không hợp lệ cho trang hiện tại.</p>
    <div class="actions">
        <a href="javascript:history.back()" class="btn btn-primary">Quay lại</a>
        <a href="/" class="btn btn-light">Trang chủ</a>
    </div>
@endsection
