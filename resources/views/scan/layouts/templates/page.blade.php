@extends('scan.layouts.app', [
    'favicon'       => $favicon ?? null,
    'mainBg'        => $mainBg ?? null,
    'class'         => null,
])

@section('content')
    <div class="container-fluid bg-transparent">
        <div class="row bg-transparent justify-content-{{ $align ?? "center" }}">
            <div
                class="
                        col-lg-{{ $form_width ?? 6 }}
                        col-md-{{ !empty($form_width) ? ((int)$form_width - 1) : 5 }}
                        col-sm-12 col-12
                        mb-5
                        {{ $form_class ?? null }}
                    "
                >

                <x-card>
                    @if (!empty($banner))
                        <x-slot:image>
                            <img src="{{ $banner }}" class="rounded-top" alt="Banner" width="100%">
                        </x-slot>
                    @endif

                    @if (isset($popErrors) && $popErrors)
                        @include('shared/alerts')
                    @endif

                    @yield('primary-content')
                </x-card>
            </div>
        </div>
    </div>
@endsection
