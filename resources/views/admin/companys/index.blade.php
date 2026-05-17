
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Công ty"
])

@section('title')
    Công ty: <span class="text-danger">{{ $total ?? 0 }}</span>
@endsection

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.companys.create') }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            @lang('forms.actions.add')
        </a>
    </div>
@endsection

@section('primary-content')
    {{ $dataTable->table() }}
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
@endpush
