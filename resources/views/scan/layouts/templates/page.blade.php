@extends('scan.layouts.app', [
    'favicon'       => $favicon ?? null,
    'mainBg'        => $mainBg ?? null,
    'class'         => null,
])

@section('content')
    <div class="container-fluid scan-page-shell">
        <div class="row justify-content-{{ $align ?? "center" }}">
            <div
                class="
                        col-lg-{{ $form_width ?? 6 }}
                        col-md-{{ !empty($form_width) ? ((int)$form_width - 1) : 5 }}
                        col-sm-12 col-12
                        mb-5
                        {{ $form_class ?? null }}
                    "
                >
                <div class="scan-page-shell__header">
                    <x-brand-lockup
                        href="{{ route('scan.index') }}"
                        theme="light"
                        pill="Scan"
                    />
                    <div class="scan-page-shell__eyebrow">{{ config('app.name') }}</div>
                </div>

                <x-card class="scan-panel">
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
