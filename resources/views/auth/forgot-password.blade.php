@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-10 rounded shadow-sm bg-light" id="login-form-row" style="margin-top: 7%;">
            <div class="row rounded shadow-sm" style="
                    background-image: url('{{ asset('assets/images/backgrounds/checkin-login.png') }}');
                    background-repeat: no-repeat;
                    background-position: 50%;
                    background-attachment: fixed;
                    background-size: contain;
                ">
                <div class="col-md-7 p-5 rounded-start bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <x-brand-lockup href="{{ route('home') }}" theme="light" class="mb-4" />
                        <div class="">
                            <h5>@lang('auth.reset_password')</h5>

                            @if (session('status'))
                                <x-alert type="success" :dismissible="true">
                                    {{ session('status') }}
                                </x-alert>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('password.email') }}" method="POST" role="form">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email" class="form-label control-label">
                                @lang('validation.attributes.email')
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                @class(['form-control', 'is-invalid' => $errors->has('email')])
                                required
                                value="{{ old('email') }}"
                            >

                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <input type="submit" class="btn btn-primary" value="@lang('auth.send_password_reset_link')">
                            <a href="{{ route('login') }}" class="ms-2">
                                <x-icon name="arrow-left" />
                                Đăng nhập
                            </a>
                        </div>
                    </form>
                </div>

                <div class="col-md-5 px-0 bg-transparent">
                    {{-- <img src="{{ asset('assets/images/backgrounds/checkin.png') }}" alt="description" width="100%"> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
