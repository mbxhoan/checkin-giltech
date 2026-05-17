
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Sự kiện"
])

@section('title')
    Báo cáo:
    <span class="text-danger">
        {{ $total }}
    </span>
@endsection

@section('buttons')

@endsection

@section('primary-content')
    <div class="my-2">
        <a href=""
            class="btn {{ request()->hasAny([
                'company_id',
                'province_id',
                'status',
                'field_date',
                'from_date',
                'to_date'
            ]) ? 'btn-outline-warning' : 'btn-warning' }}
            btn-sm align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#filterModal"
        >
            Bộ lọc
            <x-icon name="filter"/>
        </a>
        @include('admin.reports._modal-filter', [
            'modalId'       => 'filterModal',
            'title'         => "Bộ lọc",
            'submitBtn'     => "Lọc",
            'model'         => \App\Models\Event::getModel(),
            'route'         => route('admin.reports.index'),
            'companyArray'  => $companyArray,
            'proviceArray'  => $proviceArray,
        ])
    </div>

    {!! $dataTable->table() !!}
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}

    @vite([
        'resources/js/admin/events/index.js'
    ])
@endpush
