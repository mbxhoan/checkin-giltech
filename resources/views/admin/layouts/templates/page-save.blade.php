@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-0">
        <div class="row">
            <div class="{{ $colLeft ?? "col-md-6" }}">
                @yield('sub_title')
                <form id="{{ $formId ?? null }}" action="@yield('form-action', '#')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
                    <div class="d-lg-flex justify-content-between"
                    {{-- style="position: fixed; overflow: hidden;
                        background-color: #ffffff;
                        position: fixed;
                        width: 100%;" --}}
                    >
                        @if ($pageTitle)
                            <h4>{{ $pageTitle }}</h4>
                        @endif
                        <div class="ms-2 text-end">
                            <a href="@yield('form-back', '#')" class="btn btn-light mb-1">
                                <x-icon name="chevron-left" />

                                @lang('forms.actions.back')
                            </a>
                            @hasSection('form-action')
                                <button id="{{ $btnSubmitId ?? null }}" type="submit" class="btn btn-primary mb-1">
                                    <x-icon name="save" />
                                    @lang('forms.actions.update')
                                </button>
                            @endif
                            <div class="mb-1">
                                @yield('custom-buttons')
                            </div>
                        </div>
                    </div>
                    @if (!empty($model) && !$model->isNew())
                        @method('PUT')
                    @endif
                    @csrf
                    @yield('primary-content')
                    <div class="mt-lg-4 mt-3">
                        @yield('table')
                    </div>
                </form>
                <div class="mt-lg-4 mt-3">
                    @yield('customs')
                </div>
            </div>
            <div class="{{ $colRight ?? "col-md-6" }}">
                <div class="mb-2">
                    @yield('buttons')
                </div>
                @yield('secondary-content')
            </div>
        </div>
    </div>
@endsection
