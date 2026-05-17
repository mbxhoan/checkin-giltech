@extends('web.layouts.templates.page', [
    'pageTitle'         => $event->name,
    // 'favicon'           => $event->favicon ? $event->faviconUrl->getUrl('thumb') : null,
    'favicon'           => $event->favicon ? $event->faviconUrl->getUrl() : null,
    'align'             => $event->getAlignInBootstrap(),
    'form_width'        => $model->form_width,
    'banner'            => $model->banner_id ? $model->banner->getUrl() : null,
    'mainBg'            => $model->bg_desktop_id ? $model->bg_desktop->getUrl() : null,
    'popErrors'         => false,
    'openForm'          => $event->getEventSetting("ENABLE_FORM", null)->value ?? false,
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
    <div class="mb-4 pb-4">
        <div class="text-end mb-2 lang">
            @include('components.selects.languages.lang', [
                'languages'     => $model->getLanguages(),
                'event'         => $event
            ])
        </div>

        @include('web.landing_pages.components.subject', [
            'divClass'      => 'h3',
            'id'            => 'register_subject',
            'model'         => $model,
            'text'          => lang_trans("{$event->code}.register_subject", "lp", "Tiêu đề"),
        ])

        @include('web.landing_pages.components.subject', [
            'divClass'      => 'h4',
            'id'            => 'sub_register_subject',
            'text'          => lang_trans("{$event->code}.sub_register_subject", "lp", "Tiêu đề phụ"),
        ])

        {{-- @include('frontend.event.landing-page.view-html', [
            'model' => $model,
            'html'  => __(strtolower($model->code).'.header'),
        ]) --}}

        @include('web.landing_pages._form', [
            'urlSubmitForm'         => route('clients.store', [
                'slug'              => $model->slug
            ]),
            'model'                 => $model,
            'client'                => $client,
            'cfTemplate'            => $cfTemplate,
            'customFieldTemplates'  => $customFieldTemplates,
            'campaign'              => $campaign ?? null,
            'openCaptcha'           => $event->getEventSetting("ENABLE_CAPTCHA", null)->value ?? false,
        ])

        @include('web.landing_pages.components.credit', [
            // 'logo'                  => $event->logoUrl ? $event->logoUrl->getUrl('thumb') : null,
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
        'resources/js/web/landing_pages/register.js'
    ])
@endpush

@push('web_css')
    {!! $model->generateCssFromCustoms() !!}
@endpush
