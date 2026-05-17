@extends('scan.layouts.app', [
    'favicon'       => $favicon ?? null,
    'mainBg'        => $mainBg ?? null,
    'class'         => '',
])

@section('content')
    <div class="bg-transparent">
        @yield('primary-content')
    </div>
@endsection
