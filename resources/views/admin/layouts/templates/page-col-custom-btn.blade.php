@extends('admin.layouts.app')

@section('content')
    <div class="page-header d-lg-flex justify-content-between">
        @if ($pageTitle)
            <h3>{{ $pageTitle }}</h3>
        @endif

        @yield('buttons')
    </div>

    <div class="container-fluid px-0">
        <div class="row">
            <div class="{{ $colLeft ?? "col-md-6" }}">
                @yield('sub_title')

                <form id="{{ $formId ?? null }}" action="@yield('form-action')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
                    @if (!empty($buttonsTop) && $buttonsTop)
                        <div class="pull-left mb-2">
                            <a href="@yield('form-back')" class="btn btn-light">
                                <x-icon name="chevron-left" />

                                @lang('forms.actions.back')
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <x-icon name="save" />

                                @lang('forms.actions.update')
                            </button>
                        </div>
                    @endif

                    @if (!empty($model) && !$model->isNew())
                        @method('PUT')
                    @endif
                    @csrf
                    @yield('primary-content')

                    @if (empty($buttonsTop) || !$buttonsTop)
                        <div class="pull-left mt-2">
                            <a href="@yield('form-back')" class="btn btn-light">
                                <x-icon name="chevron-left" />

                                @lang('forms.actions.back')
                            </a>
                            @hasSection('form-action')
                                <button type="submit" class="btn btn-primary">
                                    <x-icon name="save" />
                                    @lang('forms.actions.update')
                                </button>
                            @endif
                            @yield('custom-buttons')
                        </div>
                    @endif
                </form>

                <div class="mt-lg-4 mt-3">
                    @yield('table')
                </div>

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
