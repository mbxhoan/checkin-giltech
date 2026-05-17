@php
    $menuCount = 0;
    $blockCount = 0;
    $features = config("info.events.features");

    if (!auth()->user()->isSysAdmin()) {
        $user = auth()->user();
        $package = $user->package->code;
        $selectedFeatures = config("info.packages.{$package}.events.features") ?? [];
        if (count($selectedFeatures)) $features = array_intersect_key($features, array_flip($selectedFeatures));
    }
@endphp

@extends('admin.layouts.templates.page-col', [
    'pageTitle' => "Chỉnh sửa sự kiện",
    'colLeft'   => 'col-md-12',
    'colRight'  => 'col-md-0',
    'formClass' => 'bg-light rounded shadow-sm p-2',
])

@section('form-action', $model->isNew() ? route('admin.events.store') : route('admin.events.update', $model))
@section('form-back', route('admin.events.index'))

@section('sub_title')
    @if (!$model->isNew())
        <h5 class="tutor-text">
            {{ ++$menuCount }}. {{ $features["e-{$menuCount}"]['name'] ?? "UNKNOWN" }}
            <input type="hidden" name="" value="{{ ++$blockCount }}">
        </h5>
    @endif
@endsection

@section('buttons')
    <div class="">
        @if (!$model->isNew())
            <a href="" id="btn-toggle-collapses">
                <x-icon name="eye"/>
            </a>
            <a href="{{ route('admin.clients.index', [
                    'event' => $model
                ]) }}"
                class="btn btn-sm btn-primary"
            >
                <x-icon name="users"/>
                Danh sách khách mời
            </a>
            <a href="{{ route('admin.clients.import', $model) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="upload"/>
                Nạp khách mời
            </a>
            <a href="{{ route('admin.clients.create', $model) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm khách mời
            </a>
            @if ($model->code === 'videc-2026')
                <a href="{{ url("/admin/events/{$model->id}/tickets") }}" class="btn btn-success btn-sm align-self-center mb-lg-0 mb-2">
                    <x-icon name="ticket" prefix="fa-solid"/>
                    Quản lý vé
                </a>
                <a href="{{ route('admin.reports.report', $model) }}" class="btn btn-outline-success btn-sm align-self-center mb-lg-0 mb-2">
                    <x-icon name="chart-column" prefix="fa-solid"/>
                    Báo cáo vé
                </a>
            @endif
            @include('admin.events._btn-clone', [
                'model'                 => $model,
                'route'                 => route('admin.events.clone', $model),
                'class'                 => 'btn btn-sm btn-primary',
                'confirm'               => "Bạn có chắc chắn muốn nhân bản sự kiện này?",
                'text'                  => 'Nhân bản',
                'icon'                  => '<i class="fa-solid fa-copy"></i>',
                'modalId'               => "event-clone-{$model->id}",
                'label'                 => 'VUI LÒNG NHẬP <b>"COPY"</b> ĐỂ XÁC NHẬN NHÂN BẢN',
                'companyArray'          => $companyArray ?? [],
                'company'               => $company ?? null,
            ])
        @endif
    </div>
@endsection

@section('primary-content')
    @include('admin/events/_form', [
        'model'         => $model,
        'company'       => $company ?? null,
        'companyArray'  => $companyArray ?? [],
        'features'      => $features,
    ])
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="row">
            <div class="col-md-6">
                {{-- 2 --}}
                <input type="hidden" name="" value="{{ ++$blockCount }}">
                @include("admin.events.features.e-".(++$menuCount), [
                    'menuCount'             => $menuCount,
                    'event'                 => $model,
                    'customFieldTemplates'  => $customFieldTemplates,
                ])
            </div>
            <div class="col-md-6">
                {{-- 3 --}}
                <input type="hidden" name="" value="{{ ++$blockCount }}">
                @include("admin.events.features.e-".(++$menuCount), [
                    'menuCount'             => $menuCount,
                    'event'                 => $model,
                    'setting'               => $setting,
                    'settings'              => $settings,
                ])
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mt-2">
                {{-- 4 --}}
                <input type="hidden" name="" value="{{ ++$blockCount }}">
                @include("admin.events.features.e-".(++$menuCount), [
                    'menuCount'             => $menuCount,
                    'event'                 => $model,
                ])
            </div>
            @if ($model->hasFeature("e-".++$menuCount))
                <input type="hidden" name="" value="{{ ++$blockCount }}">
                <div class="col-md-6 mt-2">
                    {{-- 5 --}}
                    @include("admin.events.features.e-{$menuCount}", [
                        'blockCount'            => $blockCount,
                        'menuCount'             => $menuCount,
                        'event'                 => $model,
                        'eventFiles'            => $eventFiles,
                    ])
                </div>
            @endif
            @if ($model->hasFeature("e-".++$menuCount))
                <input type="hidden" name="" value="{{ ++$blockCount }}">
                <div class="col-md-6 mt-2">
                    {{-- 6 --}}
                    @include("admin.events.features.e-{$menuCount}", [
                        'blockCount'            => $blockCount,
                        'menuCount'             => $menuCount,
                        'event'                 => $model,
                    ])
                </div>
            @endif
            @if ($model->getEventSetting("OPEN_LANDING_PAGE", null)->value ?? null)
                @if (auth()->user()->validateFeature('landing_pages'))
                    @if ($model->hasFeature("e-".++$menuCount))
                        <input type="hidden" name="" value="{{ ++$blockCount }}">
                        <div class="col-md-6 mt-2">
                            {{-- 7 --}}
                            @include("admin.events.features.e-7", [
                                'menuCount'             => $menuCount,
                                'blockCount'            => $blockCount,
                                'event'                 => $model,
                                'landingPages'          => $landingPages,
                            ])
                        </div>
                    @endif
                @endif
            @endif
            @if (auth()->user()->validateFeature('emails'))
                <input type="hidden" name="" value="{{ ++$menuCount }}">
                {{-- <input type="hidden" name="" value="{{ ++$blockCount }}"> --}}
                {{-- <div class="col-md-6 mt-3">
                    <h5 class="tutor-text bg-light text-white p-2 rounded shadow-sm">
                        <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseCampaigns" aria-controls="collapseCampaigns">
                            {{ ++$menuCount }}. Gửi mail
                        </a>
                        <a href="" class="text-sm text-warning btn-tutor" title="Thông tin về tính năng gửi email">
                            <x-icon name="circle-question" prefix="fa-solid fa-beat-fade" />
                        </a>
                    </h5>

                    <div id="campaigns">
                        @include('admin.campaigns._list', [
                            'event'         => $model,
                            'campaigns'     => $campaigns,
                        ])
                    </div>
                </div> --}}
            @endif
            @if (auth()->user()->validateFeature('cards'))
                @if ($model->hasFeature("e-".++$menuCount))
                    <input type="hidden" name="" value="{{ ++$blockCount }}">
                    <div class="col-md-6 mt-2">
                        {{-- 9 --}}
                        @include("admin.events.features.e-9", [
                            'menuCount'             => $menuCount,
                            'blockCount'            => $blockCount,
                            'event'                 => $model,
                            'cards'                 => $cards,
                        ])
                    </div>
                @endif
            @endif
            @if (auth()->user()->validateFeature('labels'))
                @if ($model->hasFeature("e-".++$menuCount))
                    <input type="hidden" name="" value="{{ ++$blockCount }}">
                    <div class="col-md-6 mt-2">
                        {{-- 10 --}}
                        @include("admin.events.features.e-10", [
                            'blockCount'            => $blockCount,
                            'menuCount'             => $menuCount,
                            'event'                 => $model,
                            'labels'                => $labels,
                        ])
                    </div>
                @endif
            @endif

            {{-- ADD FEATURE --}}
            @if ($blockCount < count($features))
                <div class="col-md-6 mt-2">
                    <a href="" class="text-sm fw-bold"
                        data-bs-toggle="modal"
                        data-bs-target="#addFeatureEvent"
                    >
                        <x-icon name="plus-square" prefix="fa-regular"/>
                        Thêm tính năng
                    </a>
                    <div class="modal fade" id="addFeatureEvent" data-bs-keyboard="true" tabindex="-1"
                        aria-labelledby="addFeatureEventLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="addFeatureEventLabel">
                                        Chọn tính năng
                                    </h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.events.update-features', $model) }}" method="POST">
                                    @csrf
                                    <div class="modal-body text-sm">
                                        @foreach ($features as $key => $feature)
                                            {{-- giữ những tính năng mặc định --}}
                                            @if (in_array($key, [
                                                "e-1",
                                                "e-2",
                                                "e-3",
                                                "e-4",
                                            ]))
                                                <input type="hidden" name="features[]" value="{{ $key }}">
                                            @else
                                                <div class="row mb-1">
                                                    <div class="col-md-12">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" name="features[]" value="{{ $key }}"
                                                                    @checked($model->hasFeature($key))
                                                                >
                                                                    {{ $feature['name'] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                            Đóng
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Lưu
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection

@section('secondary-content')

@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/events/detail.js'
    ])
@endpush
