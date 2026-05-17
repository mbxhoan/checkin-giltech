@extends('users.layout', ['tab' => 'profile'])

@section('main_content')
    <x-card>
        <h1>@lang('users.profile')</h1>

        <hr class="my-4">

        <div class="row">
            <form action="{{ route('users.update') }}" method="POST" class="col-md-6">
                @method('PATCH')
                @csrf

                <div class="form-group mb-3 row">
                    <label for="name" class="form-label col-sm-4 col-form-label">
                        @lang('users.attributes.name')
                    </label>

                    <div class="col-sm-8">
                        <input type="text" id="name" name="name" @class(['form-control', 'is-invalid' => $errors->has('name')])
                            placeholder="@lang('users.placeholder.name')" required value="{{ old('name', $user) }}">

                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-3 row">
                    <label for="email" class="form-label col-sm-4 col-form-label">
                        @lang('users.attributes.email')
                    </label>

                    <div class="col-sm-8">
                        <input type="text" id="email" name="email" @class(['form-control', 'is-invalid' => $errors->has('email')])
                            placeholder="@lang('users.placeholder.email')" required value="{{ old('email', $user) }}">

                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-3 offset-sm-4">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
                        <x-icon name="arrow-left" />
                        Quay lại
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <x-icon name="save" />

                        @lang('forms.actions.save')
                    </button>
                </div>
            </form>
            <div class="col-md-6">
                @if ($user->package_id)
                    <div class="row">
                        <div class="col-md-4">
                            Gói đang sử dụng:
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded shadow-sm fw-bold px-3 py-2">
                                {{ config("info.packages.{$user->package->code}.name") }}
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            Để nâng cấp gói, vui lòng liên hệ: <a target="_blank" href="mailto:{{ env('FROM_MAIL') }}">{{ env('FROM_MAIL') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-card>
@endsection
