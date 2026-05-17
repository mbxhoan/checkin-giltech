@extends('admin.layouts.templates.page-index', [
    'pageTitle' => 'API Logs',
])

@section('title')
    API Logs
@endsection

@section('buttons')
    <div class="text-muted small align-self-end">
        Request/response từ OnePay, web đăng ký và các API ngoài
    </div>
@endsection

@section('primary-content')
    <div class="card shadow-sm">
        <div class="card-body">
            {!! $dataTable->table() !!}
        </div>
    </div>
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}
@endpush
