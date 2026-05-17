@extends('layouts.app')

@section('content')
<div class="row justify-content-md-center m-3">
    <div class="col-md-6">
        <h1>@lang('auth.reset_password')</h1>

        <form action="{{ route('password.store') }}" method="POST" role="form">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                    value="{{ $email ?? old('email') }}"
                >

                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            @include('components.form-groups.input-group', [
                'id'                => "password",
                'model'             => null,
                'type'              => "password",
                'label'             => __('validation.attributes.password'),
                'formClass'         => 'form-group mb-3',
                'inputClass'        => 'form-control text-sm',
                'placeholder'       => __('validation.attributes.password'),
                'required'          => true,
                'autocomplete'      => 'new-password',
            ])
            @include('components.form-groups.input-group', [
                'id'                => "password_confirmation",
                'model'             => null,
                'type'              => "password",
                'label'             => __('validation.attributes.password_confirmation'),
                'formClass'         => 'form-group mb-3',
                'inputClass'        => 'form-control text-sm',
                'placeholder'       => __('validation.attributes.password_confirmation'),
                'required'          => true,
                'autocomplete'      => 'new-password',
            ])

            <div class="form-group mb-3">
                <input type="submit" class="btn btn-primary" value="@lang('auth.reset_password')">
            </div>
        </form>
    </div>
</div>
@endsection
