@extends('admin.layouts.app')

@section('content')
    <div class="page-header d-lg-flex justify-content-between align-items-start gap-3">
        <h3 class="page-header__title">
            @yield('title')
        </h3>

        <div class="page-header__actions">
            @yield('buttons')
        </div>
    </div>

    <div class="page-body @yield('pageClass')">
        @yield('primary-content')
    </div>
@endsection
