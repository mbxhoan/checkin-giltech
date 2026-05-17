
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Lịch sử"
])

@section('title')

@endsection

@section('buttons')

@endsection

@section('primary-content')
    {!! $dataTable->table() !!}
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}
@endpush
