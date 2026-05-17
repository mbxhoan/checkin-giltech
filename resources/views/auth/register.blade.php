@extends('layouts.auth')

@section('auth-content')

    <img src="{{ asset('assets/images/logo-transparent.png') }}"
         alt="Giltech Solutions"
         class="auth-form__icon">

    <h1 class="auth-form__title" style="font-size:1.35rem;">Tạo tài khoản</h1>
    <p class="auth-form__sub">Điền thông tin để đăng ký sử dụng Giltech Solutions</p>

    <form action="{{ route('register') }}" method="POST" role="form" novalidate>
        @csrf

        {{-- ── STEP 1: Thông tin cá nhân ── --}}
        <div class="form-step active" data-step="1">

            <div class="row g-2">
                @include('components.form-groups.input-group', [
                    'id'            => "company_name",
                    'model'         => null,
                    'type'          => "text",
                    'label'         => "Công ty",
                    'formClass'     => 'form-group mb-2 col-12',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => "Tên công ty của bạn",
                    'required'      => true,
                ])
            </div>

            <div class="row g-2">
                @include('components.form-groups.input-group', [
                    'id'            => "name",
                    'model'         => null,
                    'type'          => "text",
                    'label'         => "Tên của bạn",
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => __('validation.attributes.name'),
                    'required'      => true,
                ])
                @include('components.form-groups.input-group', [
                    'id'            => "position",
                    'model'         => null,
                    'type'          => "text",
                    'label'         => "Chức vụ",
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => "Chức vụ",
                ])
            </div>

            <div class="row g-2">
                @include('components.form-groups.input-group', [
                    'id'            => "email",
                    'model'         => null,
                    'type'          => "text",
                    'label'         => __('validation.attributes.email').' <span class="text-xs fw-normal text-secondary fst-italic">(dùng email công ty)</span>',
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => __('validation.attributes.email'),
                    'required'      => true,
                    'autofocus'     => true,
                ])
                @include('components.form-groups.input-group', [
                    'id'            => "phone",
                    'model'         => null,
                    'type'          => "text",
                    'label'         => __('validation.attributes.phone'),
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => __('validation.attributes.phone'),
                    'required'      => true,
                ])
            </div>

            <div class="row g-2">
                @include('components.form-groups.input-group', [
                    'id'            => "password",
                    'model'         => null,
                    'type'          => "password",
                    'value'         => old('password'),
                    'label'         => __('validation.attributes.password'),
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => __('validation.attributes.password'),
                    'required'      => true,
                ])
                @include('components.form-groups.input-group', [
                    'id'            => "password_confirmation",
                    'model'         => null,
                    'type'          => "password",
                    'value'         => old('password_confirmation'),
                    'label'         => __('validation.attributes.password_confirmation'),
                    'formClass'     => 'form-group mb-2 col-6',
                    'inputClass'    => 'form-control text-sm',
                    'placeholder'   => __('validation.attributes.password_confirmation'),
                    'required'      => true,
                ])
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ route('login') }}" style="font-size:.85rem;color:#0369a1;text-decoration:none;">
                    <x-icon name="arrow-left" /> Đăng nhập
                </a>
                <button type="button" class="auth-btn-primary next-step" style="width:auto;padding:.65rem 1.5rem;">
                    Tiếp theo <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── STEP 2: Gói & Thiết bị ── --}}
        <div class="form-step" data-step="2">

            <div class="mb-3">
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

            <p class="fw-semibold text-sm mb-2">Chọn gói dịch vụ:</p>

            @php
                $options = collect(config('info.packages'))->mapWithKeys(function ($item, $key) {
                    return [$key => "{$item['name']}"];
                });
                $selected = array_key_first(config('info.packages'));
            @endphp

            <div class="form-group form-group-package mb-3">
                <div class="input-group-package">
                    <div class="row justify-content-center g-2">
                        @foreach ($options as $key => $val)
                            <label class="form-control-label text-center col-4">
                                <div class="fw-bold text-sm">
                                    {!! config("info.packages.{$key}.full_name") !!}
                                    @if (in_array($key, ['basic', 'pro']))
                                        <div>
                                            <a href="#" class="text-xs"
                                               data-bs-toggle="collapse"
                                               data-bs-target="#collapsePackage-{{ $key }}"
                                               aria-expanded="false">
                                                Chi tiết <x-icon name="circle-info"/>
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-xs fst-italic text-secondary">
                                            {!! config("info.packages.{$key}.note") ?? "<div>&nbsp;</div>" !!}
                                        </div>
                                    @endif
                                </div>
                                <input
                                    type="radio"
                                    name="package"
                                    id="option_{{ $key }}"
                                    class="{{ $inputClass ?? 'form-check-input' }}"
                                    value="{{ $key }}"
                                    {{ $key == old($selected) ? 'checked' : ($key == "basic" ? 'checked' : '') }}
                                    {{ config("info.packages.{$key}.enable") ? '' : 'disabled' }}
                                />
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Package detail collapse panels --}}
            <div id="collapse-container" class="mb-3">
                @foreach ($options as $key => $val)
                    <div class="collapse multi-collapse bg-light rounded-3 mb-2" id="collapsePackage-{{ $key }}">
                        <div class="p-3 text-center">
                            <h6 class="fw-bold mb-0">{!! config("info.packages.{$key}.full_name") !!}</h6>
                            <div class="text-sm text-secondary">
                                <del>{{ config("info.packages.{$key}.prev_price") }}</del>
                            </div>
                            <h6 class="text-danger fw-bold">{{ config("info.packages.{$key}.price") }}</h6>
                            @foreach (config("info.packages_features") as $groupKey => $groupDetail)
                                <div class="mb-2">
                                    <div class="fw-semibold text-sm mb-1">{{ $groupDetail['name'] }}</div>
                                    @foreach (config("info.packages_features.{$groupKey}.details") as $index => $feature)
                                        <div class="text-xs">
                                            @if (in_array($index, config("info.packages.{$key}.showing_features.includes")))
                                                {{ $feature }}
                                            @else
                                                @if (in_array($index, array_keys(config("info.packages.{$key}.showing_features.specials"))))
                                                    {{ $feature }}: {{ config("info.packages.{$key}.showing_features.specials.{$index}") }}
                                                @else
                                                    <del class="fst-italic text-secondary">{{ $feature }}</del>
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

            <p class="fw-semibold text-sm mb-2">Thuê thiết bị:</p>
            <div class="row mb-3">
                @foreach (config("info.devices") as $key => $name)
                    <div class="col-6">
                        <label class="form-control-label text-sm mb-1" style="cursor:pointer;">
                            <input type="checkbox" id="devices.{{ $key }}" name="devices[{{ $key }}]" value="{{ $key }}"
                                @checked(old("devices.$key"))>
                            {{ $name }}
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="row mb-3">
                @include('components.form-groups.input-group', [
                    'id'            => "g-recaptcha-response",
                    'type'          => "recaptcha",
                    'formClass'     => 'form-group text-center col-12',
                ])
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-light prev-step" style="font-size:.875rem;">
                    <i class="fa-solid fa-arrow-left me-1"></i>Trước
                </button>
                <button type="submit" class="auth-btn-primary" style="width:auto;padding:.65rem 1.5rem;">
                    @lang('auth.register') <i class="fa-solid fa-check"></i>
                </button>
            </div>
        </div>

    </form>

    <div class="auth-footer">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập</a>
    </div>

@endsection

@push('css')
<style>
    /* Widen auth-form-wrap for register's 2-column fields */
    .auth-form-wrap { max-width: 520px; }

    /* Multi-step animation */
    .form-step { display: none; }
    .form-step.active { display: block; }
    .slide-left { animation: slideLeft .35s forwards; }
    .slide-right { animation: slideRight .35s forwards; }
    @keyframes slideLeft {
        from { transform: translateX(40px); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }
    @keyframes slideRight {
        from { transform: translateX(-40px); opacity: 0; }
        to   { transform: translateX(0);     opacity: 1; }
    }
</style>
@endpush

@push('js')
<script>
$('.next-step').on('click', function () {
    const $cur = $('.form-step.active');
    const $nxt = $cur.next('.form-step');
    $cur.removeClass('active');
    $nxt.addClass('active slide-left');
    setTimeout(() => $nxt.removeClass('slide-left'), 350);
});

$('.prev-step').on('click', function () {
    const $cur = $('.form-step.active');
    const $prv = $cur.prev('.form-step');
    $cur.removeClass('active');
    $prv.addClass('active slide-right');
    setTimeout(() => $prv.removeClass('slide-right'), 350);
});
</script>
@endpush
