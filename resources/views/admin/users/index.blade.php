@extends('admin.layouts.app', [
    'pageTitle' => 'Tài khoản',
])

@section('content')
    <div class="page-header d-lg-flex justify-content-between mb-2">
        <div class="">
            <h3>
                @lang('dashboard.users'):
                <span class="text-danger">
                    {{ $users->total() }}
                </span>
            </h3>
            <a href=""
                class="btn {{ request()->hasAny([
                    'company_id',
                    'event_id',
                    'status',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'btn-outline-warning' : 'btn-warning' }}
                btn-sm mb-lg-0 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
            >
                Bộ lọc
                <x-icon name="filter"/>
            </a>
            @include('admin.users._modal-filter', [
                'modalId'       => 'filterModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => \App\Models\User::getModel(),
                'route'         => route('admin.users.index'),
                'companyArray'  => $companyArray ?? [],
                'eventArray'    => $eventArray ?? []
            ])
        </div>
        <div class="">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-lg-0 mb-2">
                <x-icon name="plus-square" prefix="fa-regular"/>
                @lang('forms.actions.add')
            </a>
        </div>
    </div>

    @include('admin/users/_list', [
        'users' => $users,
    ])
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/users/index.js'
    ])
@endpush
