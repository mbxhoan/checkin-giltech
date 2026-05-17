@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-12 rounded shadow-sm bg-light" id="login-form-row" style="margin-top: 0%;">
            <form action="{{ route('register') }}" method="POST" role="form" class="px-0">
                <div class="row rounded shadow-sm align-items-stretch" style="
                        background-image: url('{{ asset('assets/images/backgrounds/checkin-login.png') }}');
                        background-repeat: no-repeat;
                        background-position: 50%;
                        background-attachment: fixed;
                        background-size: cover;
                    ">
                    {{-- <form action="{{ route('register') }}" method="POST" role="form" class="px-0"> --}}
                    @csrf
                    <div class="col-md-6 p-5 rounded-start bg-white">
                        <div class="form-step align-items-center active" data-step="1">
                            <div class="d-flex justify-content-between align-items-center">
                                <x-brand-lockup href="{{ route('home') }}" theme="light" class="mb-4" />
                                <div class="fst-italic text-sm">
                                    Vui lòng nhập thông tin đăng ký*
                                </div>
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "company_name",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => "Công ty",
                                    'formClass'         => 'form-group mb-3 text-sm col-md-12',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => "Công ty",
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "name",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => "Tên của bạn",
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => __('validation.attributes.name'),
                                    'required'          => true,
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "position",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => "Chức vụ",
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => "Chức vụ",
                                ])
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "email",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('validation.attributes.email').' <span class="text-xs fw-bold text-secondary fst-italic">Vui lòng dùng email công ty</span>',
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => __('validation.attributes.email'),
                                    'required'          => true,
                                    'autofocus'         => true,
                                ])

                                @include('components.form-groups.input-group', [
                                    'id'                => "phone",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('validation.attributes.phone'),
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => __('validation.attributes.phone'),
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "password",
                                    'model'             => null,
                                    'type'              => "password",
                                    'value'             => old('password'),
                                    'label'             => __('validation.attributes.password'),
                                    'formClass'         => 'mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => __('validation.attributes.password'),
                                    'required'          => true,
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "password_confirmation",
                                    'model'             => null,
                                    'type'              => "password",
                                    'value'             => old('password_confirmation'),
                                    'label'             => __('validation.attributes.password_confirmation'),
                                    'formClass'         => 'mb-3 text-sm col-md-6',
                                    'inputClass'        => 'form-control text-sm',
                                    'placeholder'       => __('validation.attributes.password_confirmation'),
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row my-3">
                                <div class="col-md-4">
                                    <a href="{{ route('login') }}" class="text-sm">
                                        <x-icon name="arrow-left" />
                                        Đăng nhập
                                    </a>
                                </div>
                                <div class="col-md-4 text-center">
                                    <button type="button" class="btn btn-primary next-step">Tiếp</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-step align-items-center" data-step="2">
                            <div class="row">
                                <div class="col-md-12 text-sm">
                                    @include('components.select', [
                                        'label'         => "Loại hình sự kiện",
                                        'fieldName'     => 'company_type',
                                        'id'            => 'company_type',
                                        'formClass'     => 'form-control mb-3 text-sm',
                                        'options'       => $companyTypes,
                                        'selected'      => null,
                                        'required'      => true,
                                    ])
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 fw-bold fst-italic text-sm mb-3">
                                    * Vui lòng chọn tham khảo các gói:
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group form-group-package mb-3">
                                        <div class="input-group-package">
                                            @php
                                                $options = collect(config('info.packages'))->mapWithKeys(function ($item, $key) {
                                                    return [$key => "{$item['name']}"];
                                                });

                                                $selected = array_key_first(config('info.packages'));
                                            @endphp
                                            <div class="row justify-content-center">
                                                @foreach ($options as $key => $val)
                                                    <label class="form-control-label text-center col-md-4">
                                                        <div class="fw-bold text-sm">
                                                            {{-- {{ $val }} --}}
                                                            {!! config("info.packages.{$key}.full_name") !!}
                                                            @if (in_array($key, ['basic', 'pro']))
                                                                <div class="">
                                                                    <a href="" class="text-xs"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapsePackage-{{ $key }}"
                                                                        aria-expanded="false"
                                                                        aria-controls="collapsePackage-{{ $key }}"
                                                                        title="Thông tin thêm về {{ $val }}"
                                                                    >
                                                                        Chi tiết
                                                                        <x-icon name="circle-info"/>
                                                                    </a>
                                                                </div>
                                                            @else
                                                                {{-- <div>&nbsp;</div> --}}
                                                                <div class="text-xs fst-italic text-secondary">
                                                                    {!! config("info.packages.{$key}.note") ?? "<div>&nbsp;</div>" !!}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs fst-italic text-secondary"></div>
                                                        <input
                                                            type="radio"
                                                            name="package"
                                                            id="option_{{ $key }}"
                                                            class="{{ $inputClass ?? 'form-check-input' }} disabled"
                                                            value="{{ $key }}"
                                                            {{ $key == old($selected) ? 'checked' : ($key == "basic" ? 'checked' : '') }}
                                                            {{ config("info.packages.{$key}.enable") ? '' : 'disabled' }}
                                                            {{-- {{ !in_array($key, ['basic']) ? 'disabled' : '' }} --}}
                                                        />
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 fw-bold fst-italic text-sm mb-3">
                                    * Lựa chọn thiết bị thuê:
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        @foreach (config("info.devices") as $key => $name)
                                            <div class="col-md-6">
                                                <div class="checkbox">
                                                    <label class="form-control-label mb-1 text-sm">
                                                        <input type="checkbox" id="devices.{{ $key }}" name="devices[{{ $key }}]" value="{{ $key }}"
                                                            @checked(old("devices.$key"))
                                                        >
                                                        {{ $name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "g-recaptcha-response",
                                    // 'label'             => 'Recaptcha',
                                    'type'              => "recaptcha",
                                    'formClass'         => 'form-group text-center col-md-12',
                                ])
                            </div>
                            <div class="form-group my-3 text-center">
                                <button type="button" class="btn btn-secondary prev-step">Trước</button>
                                <input type="submit" class="btn btn-primary" value="@lang('auth.register')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 px-0 bg-transparent">
                        <div class="d-flex flex-wrap px-0  h-100 bg-transparent" id="collapse-container">
                            @foreach ($options as $key => $val)
                                <div class="collapse bg-white multi-collapse" id="collapsePackage-{{ $key }}">
                                    <div class="py-5 px-4 text-center">
                                        <h5 class="fw-bold mb-0">
                                            {!! config("info.packages.{$key}.full_name") !!}
                                        </h5>
                                        <div class="text-sm">
                                            <del>{{ config("info.packages.{$key}.prev_price") }}</del>
                                        </div>
                                        <h6 class="text-danger fw-bold ">
                                            {{ config("info.packages.{$key}.price") }}
                                        </h6>
                                        @foreach (config("info.packages_features") as $groupKey => $groupDetail)
                                            <div class="mb-2">
                                                <div class="fw-bold mb-2">
                                                    {{ $groupDetail['name'] }}
                                                </div>
                                                {{-- @dd(config("info.packages.{$key}.showing_features")) --}}
                                                @foreach (config("info.packages_features.{$groupKey}.details") as $index => $feature)
                                                    <div class="text-xs">
                                                        @if (in_array($index, config("info.packages.{$key}.showing_features.includes")))
                                                            {{-- tính năng mặc định --}}
                                                            {{ $feature }}
                                                        @else
                                                            {{-- tính năng show dạng text --}}
                                                            @if (in_array($index, array_keys(config("info.packages.{$key}.showing_features.specials"))))
                                                                {{ $feature }}: {{ config("info.packages.{$key}.showing_features.specials.{$index}") }}
                                                            @else
                                                                <del class="fst-italic">{{ $feature }}</del>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- @foreach ($options as $key => $val)
                            <div class="collapse bg-white h-100 multi-collapse" id="collapsePackage-{{ $key }}">
                                <div class="p-4">
                                    {{ $key }}
                                </div>
                            </div>
                        @endforeach --}}
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* collapse */
        .collapse.flex-1 {
            flex: 1 1 100%;
        }
        .collapse.flex-2 {
            flex: 1 1 50%;
        }
        .collapse.flex-3 {
            flex: 1 1 33.3333%;
        }
        .multi-collapse {
            transition: height 0.35s ease; /* keep Bootstrap height transition */
            overflow: hidden;
        }
        .collapsing {
            display: none;
        }

        /* multi-step registration */
        .form-step {
        display: none;
        transition: all 0.5s ease-in-out;
        /* height: 570px; */
        /* max-height: 570px; */
        }
        .form-step.active {
        display: block;
        }

        .slide-left {
        animation: slideLeft 0.5s forwards;
        }

        .slide-right {
        animation: slideRight 0.5s forwards;
        }

        @keyframes slideLeft {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideRight {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
        }
    </style>
@endpush

@push('js')
    {{-- multi-step registration --}}
    <script>
        $('.next-step').on('click', function () {
        let current = $('.form-step.active');
        let next = current.next('.form-step');
        current.removeClass('active');
        next.addClass('active slide-left');
        setTimeout(() => next.removeClass('slide-left'), 500);
        });

        $('.prev-step').on('click', function () {
        let current = $('.form-step.active');
        let prev = current.prev('.form-step');
        current.removeClass('active');
        prev.addClass('active slide-right');
        setTimeout(() => prev.removeClass('slide-right'), 500);
        });

        $('#registrationForm').on('submit', function (e) {
        e.preventDefault();
        alert('Form submitted successfully!');
        // Submit via AJAX or send to backend here
        });
    </script>
    {{-- collapse --}}
    <script>
        function adjustCollapseWidths(preventTransition = false) {
            const container = document.getElementById('collapse-container');
            const all = container.querySelectorAll('.multi-collapse');
            const shown = container.querySelectorAll('.multi-collapse.show, .multi-collapse.collapsing');

            // Clear all previous flex classes
            all.forEach(el => {
                el.classList.remove('flex-1', 'flex-2', 'flex-3');
            });

            const flexClass =
                shown.length === 1 ? 'flex-1' :
                shown.length === 2 ? 'flex-2' : 'flex-3';

            shown.forEach(el => {
                el.classList.add(flexClass);
                // Optional: if preventTransition, disable transition during apply
                if (preventTransition) {
                    el.style.transition = 'none';
                    el.offsetHeight; // force reflow
                    el.style.transition = '';
                }
            });
        }

        // Use show event (before animation) to apply layout
        document.querySelectorAll('.multi-collapse').forEach(el => {
            el.addEventListener('show.bs.collapse', () => {
                adjustCollapseWidths(true);
            });
            el.addEventListener('hide.bs.collapse', () => {
                adjustCollapseWidths(true);
            });
            el.addEventListener('hidden.bs.collapse', adjustCollapseWidths);
            el.addEventListener('shown.bs.collapse', adjustCollapseWidths);
        });

        // Run once at page load
        adjustCollapseWidths();
    </script>
@endpush
