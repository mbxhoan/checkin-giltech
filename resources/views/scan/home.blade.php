@extends('web.layouts.templates.page', [
    'pageTitle'     => "Home",
    'form_class'    => "d-none",
    'mainBg'        => asset('assets/images/backgrounds/event.jpg'),
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "Home",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ??config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')

@endsection

@push('admin_js')

@endpush
