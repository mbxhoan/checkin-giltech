@extends('admin.layouts.app')

@section('content')
    <div class="page-header d-lg-flex justify-content-between">
        <h3>
            @yield('title')
        </h3>

        @yield('buttons')
    </div>

    <div class="@yield('pageClass')">
        @yield('primary-content')
    </div>
@endsection
