@extends('admin.layouts.app', [
    'pageTitle' => 'Tài khoản',
])

@section('content')
    {{-- <p>
        @lang('users.show') :

        @if (!$user->isNew())
            <a href="{{ route('users.show', $user) }}">
                {{ route('users.show', $user) }}
            </a>
        @endif
    </p> --}}

    @include('admin/users/_form', [
        'user'          => $user,
        'company'       => $company,
        'event'         => $event ?? null,
        'companyArray'  => $companyArray,
        'eventArray'    => $eventArray,
        'roles'         => $roles,
    ])
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/users/detail.js'
    ])
@endpush
