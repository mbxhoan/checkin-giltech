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
    <div class="scan-index">
        <div class="scan-page-headline">
            <div>
                <p class="scan-page-headline__eyebrow">Xin chào, {{ auth()->user()->name }}</p>
                <h3 class="scan-page-headline__title">Chọn sự kiện để bắt đầu check-in</h3>
                <p class="scan-page-headline__description">
                    Truy cập nhanh màn hình scan của từng sự kiện từ một giao diện gọn và tối ưu cho vận hành.
                </p>
            </div>
            <div class="scan-index__user-card">
                <div class="scan-index__user-name">
                    {{ auth()->user()->name }}
                </div>
                <div class="scan-index__user-email">
                    {{ auth()->user()->email }}
                </div>
            </div>
        </div>

        <div class="row g-3 scan-event-grid">
            @foreach ($events as $event)
                <div class="col-xl-4 col-lg-6">
                    <a href="{{ route('scan.scan', [
                            'event' => $event
                        ]) }}" class="scan-event-card"
                    >
                        <x-card class="scan-event-card__surface h-100">
                            @slot('title')
                                <div class="scan-event-card__header">
                                    <div>
                                        <div class="scan-event-card__code">{{ $event->code }}</div>
                                        <h5 class="scan-event-card__title">{{ $event->name }}</h5>
                                    </div>
                                    @if ($event->logo)
                                        <img
                                            src="{{ $event->logoUrl->getUrl() }}"
                                            class="scan-event-card__logo"
                                            alt="{{ $event->code }}"
                                            loading="lazy"
                                        >
                                    @endif
                                </div>
                            @endslot

                            <p class="scan-event-card__description">
                                {{ Str::limit($event->description, 100) }}
                            </p>

                            <div class="scan-event-card__meta">
                                <span>
                                    <x-icon name="calendar-days" />
                                    {{ humanize_date($event->from_date, 'd-m-Y') }}
                                </span>
                                <span>
                                    <x-icon name="calendar-check" />
                                    {{ humanize_date($event->to_date ?? $event->from_date, 'd-m-Y') }}
                                </span>
                            </div>

                            @if (($event->getEventSetting("ALLOW_CHECKIN_PRINT", null)->value ?? null) && (!empty($event->labels) && $event->labels->count()))
                                <div class="scan-event-card__tag">
                                    <x-icon name="print" />
                                    {{ $event->labels->first()->name }}
                                </div>
                            @endif
                        </x-card>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="scan-page-actions">
            <a href="{{ route('scan.logout') }}" class="btn btn-primary"
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
