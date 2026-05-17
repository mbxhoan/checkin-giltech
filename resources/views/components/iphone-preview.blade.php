<div class="iphone-frame">
    <div class="iphone-screen">
        <div class="iphone-notch"></div>

        {{-- Your simulated Bootstrap content goes here --}}
        <div class="iphone-content hide-scrollbar" style="
                background-color: #dddddd78; /* adjust to match image tone */
                background-image: url('{{ $mainBg ?? null }}');
                background-repeat: no-repeat;
                background-position: center center;
                background-size: cover;
            "
        >
            <div class="container-fluid bg-transparent">
                @if ((isset($openForm) && $openForm) || (!isset($openForm)))
                    <div class="row bg-transparent">
                        <div class="col-12">
                            <x-card>
                                @if (!empty($banner))
                                    <x-slot:image>
                                        <img src="{{ $banner }}" class="rounded-top" alt="Banner" width="100%">
                                    </x-slot>
                                @endif
                                <div class="mb-4 pb-4">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <a href="{{ route('admin.language_defines.generate-lang', $event) }}" class="text-sm">
                                                <x-icon name="rotate"/>
                                            </a>
                                        </div>
                                        <div class="col-md-10 mb-2 text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                @include('components.form-groups.input-group', [
                                                    'fieldName'     => "show_language_selection",
                                                    'id'            => "show_language_selection",
                                                    'model'         => $model,
                                                    'type'          => "switch",
                                                    'value'         => $model->show_language_selection ?? 0,
                                                    'formClass'     => '',
                                                    'inputClass'    => 'form-check-input text-sm toggle-language-selection',
                                                    'changeUrl'     => route('admin.landing_pages.update-show-language-selection', $model),
                                                ])
                                                @include('components.selects.languages.lang', [
                                                    'languages'     => $model->getLanguages(),
                                                    'event'         => $event,
                                                    'edit'          => true,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                    @include('web.landing_pages.components.subject', [
                                        'divClass'      => 'h3',
                                        'id'            => 'register_subject',
                                        'text'          => $model->getTranslate('register_subject', $languageCode)->translate ?? 'Tiêu đề',
                                        'edit'          => $languageCode ? true : false,
                                        'language'      => $model->getLanguageByCode($languageCode),
                                        'eventId'       => $event->id,
                                        'model'         => $model,
                                    ])
                                    @if (true)
                                        @include('web.landing_pages.components.subject', [
                                            'divClass'      => 'h4',
                                            'text'          => $model->getTranslate('sub_register_subject', $languageCode)->translate ?? "Tiêu đề phụ",
                                            'id'            => 'sub_register_subject',
                                            'edit'          => $languageCode ? true : false,
                                            'language'      => $model->getLanguageByCode($languageCode),
                                            'eventId'       => $event->id,
                                            'model'         => $model,
                                        ])
                                    @endif
                                    @if ($event->getEventSetting("ENABLE_FORM", null)->value ?? null)
                                        @include('admin.landing_pages._web-form', [
                                            'model'                 => $model,
                                            'cfTemplate'            => $cfTemplate,
                                            'customFieldTemplates'  => $customFieldTemplates,
                                        ])
                                    @else
                                        <div class="fst-italic fw-bold">
                                            Bạn chưa mở form
                                        </div>
                                    @endif
                                    <div class="row pt-4">
                                        <div class="col-12 text-center">
                                            @include('web.landing_pages.components.submit', [
                                                'btnId'         => 'btn_submit-text',
                                                'btnText'       => $model->getTranslate('btn_submit', $languageCode)->translate ?? 'Đăng ký',
                                                'btnClass'      => 'btn btn-primary',
                                                'id'            => 'btn_submit',
                                                'edit'          => $languageCode ? true : false,
                                                'model'         => $model,
                                                'language'      => $model->getLanguageByCode($languageCode),
                                                'eventId'       => $event->id,
                                            ])
                                        </div>
                                    </div>
                                    @include('web.landing_pages.components.credit', [
                                        'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
                                        'creditName'    => $model->contact_name,
                                        'creditPhone'   => $model->contact_phone,
                                        'creditEmail'   => $model->contact_email,
                                        'creditAddress' => $model->contact_address,
                                    ])
                                </div>
                            </x-card>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .iphone-frame {
        width: 500px;
        /* iPhone 14 width */
        height: 950px;
        /* iPhone 14 height */
        border: 5px solid #333;
        border-radius: 50px;
        padding: 12px;
        background: #000;
        position: relative;
        margin: 15px auto;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }
    .iphone-screen {
        background: #fff;
        width: 100%;
        height: 100%;
        border-radius: 36px;
        overflow: hidden;
        position: relative;
    }
    .iphone-notch {
        width: 200px;
        height: 30px;
        background: #000;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
    }
    .iphone-content {
        padding-top: 40px;
        /* leave space for notch */
        height: 100%;
        overflow-y: auto;
    }
</style>
