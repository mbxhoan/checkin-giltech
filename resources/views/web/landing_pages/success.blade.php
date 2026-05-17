@extends('web.layouts.templates.page', [
    'pageTitle'         => $event->name,
    // 'favicon'           => $event->favicon ? $event->faviconUrl->getUrl('thumb') : null,
    'favicon'           => $event->favicon ? $event->faviconUrl->getUrl() : null,
    'align'             => $event->getAlignInBootstrap(),
    'form_width'        => $model->form_width,
    'banner'            => $model->banner_id ? $model->banner->getUrl() : null,
    'mainBg'            => $model->bg_desktop_id ? $model->bg_desktop->getUrl() : null,
    'popErrors'         => false,
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => $event->name ?? config("metapage.title"),
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ??config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),

        // 'title'         => __(strtolower($model->code).'.register_subject'),
    ])
@endsection

@section('primary-content')
    <div class="mb-4">
        <div class="text-end mb-2 lang">
            @include('components.selects.languages.lang', [
                'languages'     => $model->getLanguages(),
                'event'         => $event
            ])
        </div>

        @include('web.landing_pages.components.subject', [
            'divClass'      => 'h3',
            'id'            => 'success_success',
            'text'          => lang_trans("{$event->code}.success_success", "lp", "Đăng ký thành công"),
        ])

        @if ($text = lang_trans("{$event->code}.sub_success_subject", "lp"))
            @include('web.landing_pages.components.subject', [
                'divClass'      => 'h4',
                'id'            => 'sub_success_subject',
                'text'          => $text,
            ])
        @endif

        @include('web.landing_pages.components.subject', [
            'divClass'      => '',
            'id'            => 'note',
            'text'          => lang_trans("{$event->code}.note", "lp", "Ghi chú"),
        ])

        @include('web.landing_pages.components.qrcode', [
            'divClass'      => '',
            'qrcode'        => route('clients.view-qrcode-by-id', [
                'id'        => $client->id
            ]),
            'qrcodeText'    => $client->qrcode,
            'alt'           => $client->qrcode,
        ])

        @include('web.landing_pages.components.download', [
            'divClass'      => '',
            'btnClass'      => 'btn btn-sm btn-danger',
            'qrcode'        => route('clients.view-qrcode-by-id', [
                'id'        => $client->id
            ]),
            'qrcodeText'    => $client->qrcode,
            'alt'           => $client->qrcode,
            'btnText'       => lang_trans("{$event->code}.btn_download", "lp", "Tải xuống"),
        ])

        @include('web.landing_pages.components.back', [
            'btnText'       => lang_trans("{$event->code}.btn_back", "lp", "Trở về"),
            'btnClass'      => 'text-sm',
            'id'            => 'btn_back',
            'edit'          => false,
            'routeBack'     => $model->getRegisterUrl(),
        ])

        @include('web.landing_pages.components.credit', [
            'logo'                  => $event->logoUrl ? $event->logoUrl->getUrl() : null,
            'creditName'            => $model->contact_name,
            'creditPhone'           => $model->contact_phone,
            'creditEmail'           => $model->contact_email,
            'creditAddress'         => $model->contact_address,
        ])
    </div>
@endsection

@push('js')
    @vite([
        'resources/js/web/landing_pages/success.js'
    ])
@endpush
