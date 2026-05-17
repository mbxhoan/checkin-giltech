@extends('admin.layouts.templates.page-save', [
    'pageTitle' => "Cập nhật",
    'colLeft'   => "col-md-12"
])

@section('form-action', $model->isNew() ? route('admin.companys.store') : route('admin.companys.update', $model))
@section('form-back', route('admin.companys.index'))

@section('buttons')
    <div class="">

    </div>
@endsection

@section('primary-content')
    <div class="mt-2">
        @include('admin/companys/_form', [
            'model'             => $model,
            'settings'          => $settings,
            'currentSettings'   => $currentSettings,
            'templates'         => $templates,
            'senders'           => $senders,
        ])
    </div>
@endsection

@if ($model->barcode_registers && $model->barcode_registers->count())
    @section('table')
        {!! $dataTable->table() !!}
    @endsection
@endif

@push('admin_js')
    @if ($model->barcode_registers && $model->barcode_registers->count())
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/companys/detail.js'
    ])
@endpush
