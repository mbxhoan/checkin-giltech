<div class="p-2 bg-light rounded shadow-sm mb-3">
    <div class="row">
        <div class="col-md-4">
            <h5>
                1. Thông tin:
            </h5>
        </div>
        <div class="col-md-8 text-end">
            <a href="" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#cloneLpModal">
                Clone
                <x-icon name="clone"></x-icon>
            </a>
        </div>
    </div>
    <div class="row mb-2 text-sm">
        <div class="col-md-12">
            @if (!$model->isNew() && $model->status == $model::STATUS_ACTIVE)
                Xem Landing page:
                <a target="_blank" href="{{ $model->getRegisterUrl() }}" id="lp-link">{{ $model->getRegisterUrl() }}</a>
                <button type="button" class="input-group-text btn btn-sm text-primary p-0" data-clipboard-target="#lp-link">
                    <x-icon name="clipboard" prefix="fa-regular" />
                </button>
                <a target="_blank" href="{{ $model->getRegisterUrl() }}" class="input-group-text btn btn-sm text-primary p-0">
                    <x-icon name="arrow-up-right-from-square" />
                </a>
                <a href="" class="input-group-text btn btn-sm text-primary p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#qrcodeLinkLandingPage"
                >
                    <x-icon name="qrcode" />
                </a>
                <div class="modal fade" id="qrcodeLinkLandingPage" tabindex="-1" aria-labelledby="qrcodeLinkLandingPageLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="qrcodeLinkLandingPageLabel">
                                    Link
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-start">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        Quét mã trên điện thoại để truy cập landing page:
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3 text-center">
                                        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->generate($model->getRegisterUrl())) !!} ">
                                        {{-- {!!
                                            QrCode::format('png')
                                                ->size(250)
                                                ->margin(2)
                                                ->generate($model->getRegisterUrl())
                                        !!} --}}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "slug",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->slug,
            'label'             => "Tên",
            'formClass'         => "mb-3 col-md-4",
            'placeholder'       => 'slug',
            'required'          => true,
            'readonly'          => $model->isNew() ? false : true,
        ])
        <div class="mb-3 col-md-4">
            @include('components.select', [
                'label'         => "Trạng thái",
                'id'            => 'status',
                'fieldName'     => 'status',
                'options'       => $model->getStatues(),
                'selected'      => $model->status,
            ])
        </div>
    </div>
    <div class="row">
        <div class="mb-3 col-md-4">
            @include('components.select', [
                'label'         => "Vị trí form",
                'id'            => 'align',
                'fieldName'     => 'align',
                'options'       => $event->getAligns(),
                'selected'      => $model->align,
            ])
        </div>
        <div class="mb-3 col-md-4">
            @include('components.select', [
                'label'         => "Chiều rộng form",
                'id'            => 'form_width',
                'fieldName'     => 'form_width',
                'options'       => $model->getFormWidths(),
                'selected'      => $model->form_width,
            ])
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="languages">
                            Ngôn ngữ
                        </label>
                        @foreach ($languages as $language)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="languages[]" id="languages" value="{{ $language->id }}"
                                        @checked($model->hasLanguage($language->id))
                                    >
                                        {{ ucfirst($language->name) }}
                                </label>
                            </div>
                        @endforeach
                        @error('languages')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="">
                        Gửi mail
                    </div>
                    @if ($registerSendMail)
                        @if (!count($campaignArray))
                            {{-- <span class="fst-italic text-danger text-xs">
                                Bạn chưa setup campaign gửi mail cho sự kiện này
                            </span> --}}
                            <a target="_blank" href="{{ route('admin.campaigns.create', $event) }}" class="text-xs"
                            >
                                <x-icon name="plus-square" prefix="fa-regular"/>
                                Tạo campaign
                            </a>
                        @else
                            @foreach ($languages as $language)
                                <div class="row mb-2">
                                    @include('components.select', [
                                        'formClass'     => 'col-md-9',
                                        'id'            => "campaign_ids.$language->code",
                                        'fieldName'     => "campaign_ids[{$language->code}]",
                                        'options'       => $campaignArray,
                                        'selected'      => $model->landingPageCampaigns->where('lang', $language->code)->first()->campaign_id ?? null,
                                        'required'      => false,
                                    ])
                                    @if ($model->landingPageCampaigns->where('lang', $language->code)->first()->campaign_id ?? null)
                                        @include('components.btn-edit', [
                                            'route'         => route('admin.campaigns.edit', [
                                                'event_id'  => $event->id,
                                                'campaign'  => $model->landingPageCampaigns->where('lang', $language->code)->first()->campaign
                                            ]),
                                            'class'         => 'col-md-3 text-xs',
                                        ])
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @else

                    @endif
                </div>
                <div class="col-md-4">
                    <div class="">
                        Chạy thiệp
                    </div>
                    @if (!empty($cardArray) && count($cardArray))
                        @foreach ($languages as $language)
                            <div class="row mb-2">
                                @include('components.select', [
                                    'formClass'     => 'col-md-9',
                                    'id'            => "card_ids.$language->code",
                                    'fieldName'     => "card_ids[{$language->code}]",
                                    'options'       => $cardArray,
                                    'selected'      => $model->landingPageCards->where('lang', $language->code)->first()->card_id ?? null,
                                    'required'      => false,
                                ])
                                @if ($model->landingPageCards->where('lang', $language->code)->first()->card_id ?? null)
                                    @include('components.btn-edit', [
                                        'route'         => route('admin.cards.edit', [
                                            'card'      => $model->landingPageCards->where('lang', $language->code)->first()->card
                                        ]),
                                        'class'         => 'col-md-3 text-xs',
                                    ])
                                @endif
                            </div>
                        @endforeach
                    @else
                        {{-- <span class="fst-italic text-danger text-xs">
                            Chưa có mẫu thiệp/thư mời
                        </span> --}}
                        <a target="_blank" href="{{ route('admin.cards.create', $event) }}" class="text-xs"
                        >
                            <x-icon name="plus-square" prefix="fa-regular"/>
                            Tạo thiệp
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="p-2 bg-light rounded shadow-sm mb-3">
    <h5>
        2. Template:
    </h5>
    <div class="form-group form-group-template_id">
        <div class="input-group-template_id">
            <div class="row justify-content-center">
                @foreach ($model->getTemplates() as $key => $detail)
                    <label class="form-control-label text-center text-sm col-md-4">
                        <div class="mb-2">
                            <img src="{{ asset($detail['path']) }}" alt="{{ $detail['name'] }}" width="55px">
                        </div>
                        <div class="mb-1 fw-bold">
                            {{ $detail['text'] }}
                        </div>
                        <input
                            type="radio"
                            name="template_id"
                            id="option_{{ $key }}"
                            class="form-check-input"
                            value="{{ $key }}"
                            @checked($model->template_id == $key || $key == 1)
                        />
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="p-2 bg-light rounded shadow-sm mb-3">
    <h5>
        3. Hình ảnh:
    </h5>
    <div class="row">
        @foreach ($model->getMediaFields() as $field => $attr)
            <div class="col-md-4 col-sm-6 col-6 mb-2">
                @if (count($attr))
                    @include('components.form-groups.input-group', [
                        'id'        => $field,
                        'label'     => $attr['label'],
                        'model'     => $model,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2'
                    ])
                    @if ($model->$field)
                        <div class="w-100 text-center">
                            <a href="{{ $attr['object']->getUrl() }}" class="w-100" target="_blank">
                                <img src="{{ $attr['object']->getUrl() }}" alt="{{ $attr['object']->name }}" width="100">
                            </a>

                        <div class="mt-2 text-center">
                                <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#{{ $field }}-{{ $model->id }}">
                                    <x-icon name="clipboard" prefix="fa-regular" />
                                </button>

                                <a href="{{ route('admin.media.show', $attr['object']) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                    <x-icon name="download" />
                                </a>
                        </div>

                        <input type="text" id="{{ $field }}-{{ $model->id }}" value="{{ $attr['object']->getUrl() }}" style="opacity: 0;">
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="p-2 bg-light rounded shadow-sm">
    <h5>
        4. Credit:
    </h5>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "contact_name",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Tên đại diện",
            'formClass'         => 'mb-3 col-md-3',
            'placeholder'       => "Họ tên",
        ])
        @include('components.form-groups.input-group', [
            'id'                => "contact_phone",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Số điện thoại",
            'formClass'         => 'mb-3 col-md-3',
            'placeholder'       => "Số điện thoại",
        ])
        @include('components.form-groups.input-group', [
            'id'                => "contact_email",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Email",
            'formClass'         => 'mb-3 col-md-3',
            'placeholder'       => "Email",
        ])
        @include('components.form-groups.input-group', [
            'id'                => "contact_address",
            'model'             => $model,
            'type'              => "text",
            'label'             => "Địa chỉ",
            'formClass'         => 'mb-3 col-md-3',
            'placeholder'       => "Địa chỉ",
        ])
    </div>
</div>
@include('components.form-groups.input-group', [
    'id'                => "id",
    'fieldName'         => "id",
    'value'             => $model->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "event_id",
    'fieldName'         => "event_id",
    'value'             => $event->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
