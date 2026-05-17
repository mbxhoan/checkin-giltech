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

                    <div class="">
                        <img src="{{ config('info.page.logo_1.internal_path') }}" alt="{{  __('dashboard.dashboard') }}" width="40%">
                    </div>

                    <form action="{{ route('login') }}" method="POST" role="form">
                        @csrf

                        @include('components.form-groups.input-group', [
                            'id'                => "email",
                            'model'             => null,
                            'type'              => "text",
                            'label'             => __('validation.attributes.email'),
                            'formClass'         => 'form-group mb-3',
                            'placeholder'       => __('validation.attributes.email'),
                            'required'          => true,
                            'autofocus'         => true,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "password",
                            'model'             => null,
                            'type'              => "password",
                            'label'             => __('validation.attributes.password'),
                            'formClass'         => 'form-group mb-3',
                            'placeholder'       => __('validation.attributes.password'),
                            'required'          => true,
                        ])
                        <div class="d-flex justify-content-between">
                            <div class="form-group mb-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" @checked(old('remember'))>
                                        @lang('auth.remember_me')
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <a href="{{ route('register') }}" class="">
                                    @lang('auth.register')?
                                </a>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <input type="submit" class="btn btn-primary" value="@lang('auth.login')">
                            <a href="{{ route('password.request') }}" class="btn btn-link">
                                @lang('auth.forgotten_password')
                            </a>
                        </div>
                    </form>
                </div>

                <div class="col-md-6 px-0 bg-transparent">
                    {{-- <img src="{{ asset('assets/images/backgrounds/checkin.png') }}" alt="description" width="100%"> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
