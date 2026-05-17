@extends('scan.layouts.templates.page', [
    'pageTitle'         => "Danh sách sự kiện",
    'favicon'           => null,
    'popErrors'         => true,
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "Danh sách sự kiện",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ??config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="mb-4">
        <div class="d-flex justify-content-between">
            <div class="text-sm">
                <p>
                    Hi, {{ auth()->user()->name }}!
                </p>
                <h3>
                    Chọn sự kiện:
                    {{-- <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                        <x-icon name="plus-square" prefix="fa-regular"/>
                    </a> --}}
                </h3>
            </div>
            <div class="text-sm text-end">
                <div class="">
                    {{ auth()->user()->name }}
                </div>
                <div class="">
                    {{ auth()->user()->email }}
                </div>
            </div>
        </div>
        <div class="row">
            @foreach ($events as $event)
                <div class="col-lg-4 mb-lg-3 mb-2">
                    <a href="{{ route('scan.scan', [
                            'event' => $event
                        ]) }}" class=""
                    >
                        <div class="card">
                            <x-card>
                                @slot('title')
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mt-2 me-2">{{ $event->code }}</h5>
                                        @if ($event->logo)
                                            {{-- <img src="{{ $event->logoUrl->getUrl('thumb') }}" class="" alt="{{ $event->code }}" width="20%" height="auto"> --}}
                                            <img src="{{ $event->logoUrl->getUrl() }}" class="" alt="{{ $event->code }}" width="20%" height="auto">
                                        @endif
                                    </div>
                                @endslot
                                <span class="mt-2 text-sm">{{ $event->name }}</span>
                                <p class="text-sm">
                                    @if (($event->getEventSetting("ALLOW_CHECKIN_PRINT", null)->value ?? null) && (!empty($event->labels) && $event->labels->count()))
                                        <x-icon name="print" />
                                        {{ $event->labels->first()->name }}
                                    @endif
                                </p>
                                <p class="card-text">
                                    {{ Str::limit($event->description, 100) }}
                                </p>
                                <p class="card-text text-xs">
                                    <small class="text-muted">Từ {{ humanize_date($event->from_date, 'd-m-Y') }}</small>
                                    <small class="text-muted">đến {{ humanize_date($event->from_date, 'd-m-Y') }}</small>
                                </p>
                            </x-card>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-5 text-end">
            {{-- <a href="" class="btn btn-xs btn-secondary">
                <x-icon name="gear" />
            </a> --}}
            <a href="{{ route('scan.logout') }}" class="btn btn-xs btn-primary"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            >
                @lang('auth.logout')
                <x-icon name="right-from-bracket" />
            </a>
            <form id="logout-form" class="d-none" action="{{ route('scan.logout') }}" method="POST">
                {{ csrf_field() }}
            </form>
        </div>
    </div>
@endsection

@push('js')
    @vite([
        // 'resources/js/scan/landing_pages/register.js'
    ])
@endpush

@push('scan_css')

@endpush
