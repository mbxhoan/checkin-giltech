@extends('web.layouts.app')

@section('content')
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

            <button type="submit" class="btn btn-primary">
                <x-icon name="save" />

                @lang('forms.actions.update')
            </button>
        </div>
    </form>
@endsection
