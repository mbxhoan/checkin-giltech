@extends('layouts.app')

@section('title', '405 Method Not Allowed')

@section('content')
    <div class="bg-white rounded shadow-sm py-5 mt-5 w-50 mx-auto">
        <div class="text-center">
            <h1>405</h1>
            <p>Phương thức HTTP không được hỗ trợ cho link này.</p>
            <a href="{{ url()->previous() }}" class="btn btn-primary">Quay lại</a>
        </div>
    </div>
@endsection
