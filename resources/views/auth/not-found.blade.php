@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-9 rounded shadow-sm bg-light" id="login-form-row" style="margin-top: 7%;">
            <div class="row rounded shadow-sm" style="
                    background-image: url('{{ asset('assets/images/backgrounds/checkin-login.png') }}');
                    background-repeat: no-repeat;
                    background-position: 50%;
                    background-attachment: fixed;
                    background-size: contain;
                ">
                <div class="col-md-6 p-5 rounded-start bg-white">
                    {{-- <h1>@lang('auth.login')</h1> --}}

                    <div class="d-flex justify-content-between">
                        <x-brand-lockup href="{{ route('home') }}" theme="light" class="mb-4" />

                    </div>

                    <div class="mt-4">
                        <h4>Không tìm thấy thông tin của bạn!</h4>
                    </div>

                    <a href="{{ route('login') }}" class="">
                        <x-icon name="arrow-left" />
                        Quay về đăng nhập
                    </a>
                </div>

                <div class="col-md-6 px-0 bg-transparent">
                </div>
            </div>
        </div>
    </div>
@endsection
