<div class="text-end mb-2 lang">
    @include('components.selects.languages.lang', [
        'languages'     => $model->getLanguages(),
        'event'         => $event,
        'edit'          => true,
    ])
</div>
@include('web.landing_pages.components.subject', [
    'divClass'      => 'h3',
    'id'            => 'success_subject',
    'text'          => $model->getTranslate('success_subject', $languageCode)->translate ?? "Đăng ký thành công",
    'edit'          => $languageCode ? true : false,
    'language'      => $model->getLanguageByCode($languageCode),
    'eventId'       => $event->id,
    'model'         => $model,
])
@if (true)
    @include('web.landing_pages.components.subject', [
        'divClass'      => 'h4',
        'text'          => $model->getTranslate('sub_success_subject', $languageCode)->translate ?? "Tiêu đề phụ",
        'id'            => 'sub_success_subject',
        'edit'          => $languageCode ? true : false,
        'language'      => $model->getLanguageByCode($languageCode),
        'eventId'       => $event->id,
        'model'         => $model,
    ])
@endif
@if (true)
    @include('web.landing_pages.components.subject', [
        'divClass'      => '',
        'id'            => 'note',
        'text'          => lang_trans("{$event->code}.note", "lp", "Ghi chú"),
        'id'            => 'note',
        'edit'          => $languageCode ? true : false,
        'language'      => $model->getLanguageByCode($languageCode),
        'eventId'       => $event->id,
        'model'         => $model,
    ])
@endif
@include('web.landing_pages.components.qrcode', [
    'divClass'      => '',
    'qrcode'        => route('get-placeholder-qrcode'),
    'qrcodeText'    => "<qrcode>",
    'alt'           => "<qrcode>",
])
@include('web.landing_pages.components.download', [
    'divClass'      => '',
    'btnClass'      => 'btn btn-sm btn-danger',
    'qrcode'        => route('get-placeholder-qrcode'),
    'qrcodeText'    => "qrcode",
    'alt'           => "qrcode",
    'btnText'       => $model->getTranslate('btn_download', $languageCode)->translate ?? "Tải xuống",
    'edit'          => $languageCode ? true : false,
    'id'            => 'btn_download',
    'language'      => $model->getLanguageByCode($languageCode),
    'eventId'       => $event->id,
    'model'         => $model,
])
@include('web.landing_pages.components.back', [
    'btnText'       => $model->getTranslate('btn_back', request()->lang)->translate ?? 'Trở về',
    'btnClass'      => 'text-sm',
    'edit'          => $languageCode ? true : false,
    'id'            => 'btn_back',
    'language'      => $model->getLanguageByCode($languageCode),
    'eventId'       => $event->id,
    'model'         => $model,
    'routeBack'     => route(Route::currentRouteName(), array_merge(request()->route()->parameters(), [
        'lang'      => $languageCode ?? null,
    ])),
])
@include('web.landing_pages.components.credit', [
    // 'logo'          => $event->logoUrl ? $event->logoUrl->getUrl('thumb') : null,
    'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
    'creditName'    => $model->contact_name,
    'creditPhone'   => $model->contact_phone,
    'creditEmail'   => $model->contact_email,
    'creditAddress' => $model->contact_address,
])

