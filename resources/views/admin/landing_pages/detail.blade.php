@extends('admin.layouts.templates.page-save-scrollable', [
    'pageTitle'     => "Chỉnh sửa landing page",
    'colLeft'       => 'col-md-6',
    'colRight'      => 'col-md-6 pt-1',
    'buttonsTop'    => true,
])

@section('form-action', $model->isNew() ? route('admin.landing_pages.store') : route('admin.landing_pages.update', $model))
@section('form-back', route('admin.events.edit', $event))

@if (!$model->isNew() && $model->status == $model::STATUS_ACTIVE)
    {{-- @section('custom-buttons')
        <a target="_blank" href="{{ $model->getRegisterUrl() }}" class="btn btn-warning text-white">
            <x-icon name="arrow-up-right-from-square" />
            Landing page
        </a>
    @endsection --}}
@endif

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.landing_pages.index') }}" class="btn btn-primary btn-sm">
            <x-icon name="list"/>
            Danh sách Landing pages
        </a>
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
        <a href="{{ route('admin.landing_pages.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
    </div>
@endsection

@section('primary-content')
    @include('admin/landing_pages/_form', [
        'event'             => $event,
        'model'             => $model,
        'languages'         => $languages,
        'registerSendMail'  => $registerSendMail ?? false,
    ])
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="mt-2 bg-light rounded shadow-sm p-2">
            <h5>
                5. Thiết lập
            </h5>
            @php
                $childSettings = $settings->where('parent_id', '!=', null);
            @endphp
            @foreach ($settings as $setting)
                @if (empty($setting->parent_id))
                    @include('admin.event_settings._setting', [
                        'event'     => $event,
                        'setting'   => $setting,
                    ])
                    @foreach ($childSettings as $childSetting)
                        @if ($childSetting->parent_id == $setting->id)
                            @include('admin.event_settings._setting', [
                                'event'     => $event,
                                'setting'   => $childSetting,
                                'isChild'   => true,
                            ])
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <div class="modal fade" id="cloneLpModal" tabindex="-1" aria-labelledby="cloneLpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cloneLpModalLabel">
                            Nhân bản Landing page
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form
                        action="{{ route('admin.landing_pages.clone', $model) }}"
                        method="POST" class="form-inline">
                        @csrf
                        <div class="modal-body text-start">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    @include('components.select', [
                                        'label'         => "Sự kiện",
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => $model->event_id,
                                        'placeholder'   => null,
                                        'required'      => true,
                                    ])
                                </div>
                                @include('components.form-groups.input-group', [
                                    'id'                => "name",
                                    'fieldName'         => "name",
                                    'value'             => $model->name,
                                    'label'             => "Tên (Slug) cho landing page mới",
                                    'placeholder'       => 'slug',
                                    'type'              => "text",
                                    'formClass'         => 'mb-3 col-md-12',
                                ])
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text-sm">
                                        Lưu ý: Nhân bản landing page không đi kèm với email, vì vậy hãy nhớ chọn lại nội dung mail cho mẫu landing page mới sau khi nhân bản thành công nhé
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                            <button type="submit" class="btn btn-primary">Xác nhận nhân bản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        <div class="row justify-content-start align-items-center px-1 px-2">
            @foreach ([
                'desktop'   => 'Form',
                'mobile'    => 'Mobile',
            ] as $screen => $name)
                <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query(array_merge(request()->query(), ['screen' => $screen])) }}"
                    class="col border p-2 btn btn-xs btn-{{ (empty(request()->screen) && $screen == "desktop") ? "primary" : (request()->screen == $screen ? "primary" : "light") }}"
                >
                    {{ $name }}
                </a>
            @endforeach
        </div>
        @if (request()->screen == "mobile")
            <div id="iphone-preview">
                <x-iphone-preview
                    :openForm="$openForm"
                    :languageCode="request()->lang ?? ($language->code ?? null)"
                    :openCaptcha="$openCaptcha"
                    :model="$model"
                    :event="$event"
                    :cfTemplate="$cfTemplate"
                    :customFieldTemplates="$event->getCustomFieldTemplates(true, true)"
                    :mainBg="optional($model->bg_mobile)->getUrl()"
                    :banner="optional($model->banner)->getUrl()"
                />
            </div>
        @else
            <x-card>
                @if (!empty($model->banner_id))
                    <x-slot:image>
                        <img src="{{ $model->banner->getUrl() }}" class="rounded-top" alt="Banner" width="100%">
                    </x-slot>
                @endif
                @if (request()->is_success)
                    @include('admin.landing_pages._success', [
                        'model'                 => $model,
                        'event'                 => $event,
                        'client'                => $client,
                        'cfTemplate'            => $cfTemplate,
                        'customFieldTemplates'  => $customFieldTemplates,
                        'languageCode'          => request()->lang ?? ($language->code ?? null),
                        'formClasses'           => request()->lang ? 'col-md-10' : 'col-md-12',
                    ])
                @else
                    @include('admin.landing_pages._landing_page', [
                        'model'                 => $model,
                        'event'                 => $event,
                        'client'                => $client,
                        'cfTemplate'            => $cfTemplate,
                        'customFieldTemplates'  => $customFieldTemplates,
                        'languageCode'          => request()->lang ?? ($language->code ?? null),
                        'formClasses'           => request()->lang ? 'col-md-10' : 'col-md-12',
                        'openCaptcha'           => $openCaptcha,
                    ])
                @endif
            </x-card>
        @endif
    @endif
@endsection

@push('admin_js')
    {{-- <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script> --}}
    @vite([
        'resources/js/admin/landing_pages/detail.js'
    ])
@endpush
