@extends('web.layouts.templates.page', [
    'pageTitle'     => "Home",
    'form_class'    => "",
    'mainBg'        => asset('assets/images/backgrounds/event.jpg'),
    'openForm'      => 1,
    'form_width'    => 6,
    'align'         => 'center',
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "QRCODES",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ??config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="row">
        <div class="col-12">
            <h5 class="fw-bold text-center">
                {{ $client->type }}
            </h5>
        </div>
    </div>
    <div class="row">
        <div class="col-12 fw-bold">
            Đại diện
        </div>
    </div>
    <div class="row align-items-center  ">
        <div class="col-md-6 col-8 text-sm">
            <div class="">
                <b>Tên:</b> {{ $client->name }}
            </div>
            <div class="">
                <b>Email:</b> {{ $client->email }}
            </div>
        </div>
        <div class="col-md-6 col-4 text-center">
            <img src="{{ route('clients.view-qrcode-by-id', [
                    'id' => $client->id
                ]) }}" alt="{{ $client->qrcode }}"
                width="40%"
                loading="lazy"
            >
            <div class="">
                <a class="text-decoration-none btn btn-primary btn-xs"
                    href="{{ route('clients.view-qrcode-by-id', [
                        'id' => $client->id
                    ]) }}"
                    title="Download {{ $client->qrcode }}"
                    alt="{{ $client->qrcode }}"
                    download="{{ basename($client->img_qrcode) }}"
                >
                    <x-icon name="download" />
                </a>
            </div>
        </div>
    </div>
    <div class="row mt-3 mb-2 align-items-center fw-bold">
        <div class="col-6">
            Nhóm <span class="text-danger">({{ !empty($clients) ? $clients->count() : 0 }})</span>
        </div>
        <div class="col-6 text-end">
            <a class="text-decoration-none btn btn-primary btn-xs"
                href="{{ route('qrcodes.download-images', [
                    'eventCode' => $client->event_code,
                    'type'      => $client->type,
                    'except'    => $client->qrcode,
                ]) }}"
                title="Download qrcodes"
            >
                <x-icon name="download" />
                Tải hàng loạt qrcodes
            </a>
        </div>
    </div>
    @if (!empty($clients))
        @foreach ($clients as $index => $client)
            <div class="row align-items-center">
                <div class="col text-center text-xs">
                    {{ ++$index }}
                </div>
                <div class="col-md-5 col-7 text-sm">
                    <div class="">
                        <b>Tên:</b> {{ $client->name }}
                    </div>
                    <div class="">
                        <b>Email:</b> {{ $client->email }}
                    </div>
                </div>
                <div class="col-md-6 col-4 text-center">
                    {{-- <img src="{{ route('clients.view-qrcode-by-id', [
                            'id' => $client->id
                        ]) }}" alt="{{ $client->qrcode }}"
                        width="40%"
                        loading="lazy"
                    > --}}
                    <div class="">
                        <a class="text-decoration-none btn btn-primary btn-xs"
                            href="{{ route('clients.view-qrcode-by-id', [
                                'id' => $client->id
                            ]) }}"
                            target="_blank"
                            title="View {{ $client->qrcode }}"
                            alt="{{ $client->qrcode }}"
                        >
                            <x-icon name="eye" />
                        </a>
                        <a class="text-decoration-none btn btn-primary btn-xs"
                            href="{{ route('clients.view-qrcode-by-id', [
                                'id' => $client->id
                            ]) }}"
                            title="Download {{ $client->qrcode }}"
                            alt="{{ $client->qrcode }}"
                            download="{{ basename($client->img_qrcode) }}"
                        >
                            <x-icon name="download" />
                        </a>
                    </div>
                </div>
            </div>
            <hr>
        @endforeach
    @else
        <div class="fst-italic text-sm">
            Không có khách hàng nào cùng nhóm
        </div>
    @endif
@endsection

@push('admin_js')

@endpush
