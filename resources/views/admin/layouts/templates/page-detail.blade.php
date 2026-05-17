@extends('admin.layouts.app')

@section('content')
    <div class="page-header d-lg-flex justify-content-between align-items-start gap-3">
        @if ($pageTitle)
            <h3 class="page-header__title">{{ $pageTitle }}</h3>
        @endif

        <div class="page-header__actions">
            @yield('buttons')
        </div>
    </div>

    <div class="container-fluid px-0">
        <div class="row">
            <div class="{{ $colLeft ?? "col-md-6" }}">
                <form action="@yield('form-action')" method="POST" enctype="multipart/form-data">
                    @if (!empty($model) && !$model->isNew())
                        @method('PUT')
                    @endif
                    @csrf
                    @yield('primary-content')

                    <div class="pull-left">
                        <a href="@yield('form-back')" class="btn btn-light">
                            <x-icon name="chevron-left" />

                            @lang('forms.actions.back')
                        </a>
                        @hasSection('form-action')
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <x-icon name="save" />
                                @lang('forms.actions.update')
                            </button>
                        @endif
                    </div>
                    <div class="mt-lg-4 mt-3">
                        @yield('table')
                    </div>
                </form>

                <div class="mt-lg-4 mt-3">
                    @yield('customs')
                </div>
            </div>

            <div class="{{ $colRight ?? "col-md-6" }}">
                @yield('secondary-content')
            </div>
        </div>
    </div>
@endsection
