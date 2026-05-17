@php
    $index = 1;
@endphp

@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => "Giao diện checkin",
    'colLeft'       => 'col-md-7',
    'colRight'      => 'col-md-5',
    'buttonsTop'    => true,
])

{{-- @section('form-action', $model->isNew() ? route('admin.clients.store') : route('admin.clients.update', $model)) --}}
@section('form-back', route('admin.events.edit', $event))

@section('buttons')
    <div class="buttons">
        {{-- @if (!$model->isNew())
            <a href="{{ route('admin.clients.destroy', [
                    'client' => $model
                ]) }}" class="btn btn-sm btn-danger align-self-center"
            >

                <x-icon name="trash" />
                Xoá
            </a>
        @endif --}}
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
        <a class="btn btn-sm btn-primary align-self-center" target="_blank" href="{{ $event->getScanLink() }}">
            Checkin
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="bg-light rounded shadow-sm pb-5 mt-2">
        <div class="row justify-content-start align-items-center px-1 px-2">
            <div class="col-md-2">
                Thông báo:
            </div>
            @foreach ($messages as $msg => $msgAttr)
                <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query([
                        'screen'      => $defaultScreen,
                        'msg'         => $msg,
                    ]) }}"
                    class="col btn btn-xs {{ $defaultMsg == $msg ? "btn-primary" : "btn-outline-primary" }}"
                >
                    {{ $msgAttr['text'] }}
                </a>
            @endforeach
            {{-- <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query([
                        'screen'      => $defaultScreen,
                    ]) }}"
                class="col btn btn-xs {{ $defaultMsg == $msg ? "btn-outline-danger" : "btn-outline-danger" }}"
            >
                X
            </a> --}}
        </div>
        <div class="row justify-content-start align-items-center px-1 px-2">
            @foreach ($screens as $screen => $screenAttr)
                <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query([
                        'screen'      => $screen,
                        'msg'         => $defaultMsg,
                    ]) }}"
                    class="col border p-2 btn btn-{{ $defaultScreen == $screen ? "secondary" : "light" }}"
                >
                    {{ $screenAttr }}
                </a>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12" id="backgroundContainer">
                @include('admin.checkins._background', [
                    'event'                 => $event,
                    'mainBg'                => $mainBg ?? null,
                    'screen'                => $defaultScreen,
                    'customFieldTemplates'  => $customFieldTemplates,
                    'msg'                   => $defaultMsg,
                    'messages'              => $messages,
                    'customCheckinMessages' => $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [],
                ])
            </div>
        </div>
    </div>
@endsection

@section('secondary-content')
    <div class="bg-light rounded shadow-sm p-2">
        @include('admin.checkins._medias', [
            'event'     => $event,
            'screen'    => $defaultScreen,
            'audio'     => $audio,
        ])
    </div>
    <div class="mt-2 bg-light rounded shadow-sm p-2">
        <h6>
            {{ ++$index }}. Thông báo checkin
        </h6>
        @if ($defaultMsg && $defaultMsg != "none")
            @include('admin.checkins._messages', [
                'event'                 => $event,
                'screen'                => $defaultScreen,
                'msg'                   => $defaultMsg,
                'messages'              => $messages,
                'customCheckinMessages' => $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [],
            ])
        @endif
    </div>
    @if (in_array($defaultMsg, [
        'success',
        'duplicated'
    ]) || (empty($defaultMsg) || in_array($defaultMsg, [
        "none"
    ])))
        <div class="mt-2 bg-light rounded shadow-sm p-2">
            <h6>
                {{ ++$index }}. Thiết lập hiển thị
            </h6>
            <div class="row mt-3">
                @if (($customFieldTemplates && $customFieldTemplates->count()))
                    @include('admin.checkins.custom_field_templates._list', [
                        'event'                 => $event,
                        'customFieldTemplates'  => $customFieldTemplates,
                        'screen'                => $defaultScreen,
                    ])
                @endif
            </div>
        </div>
    @endif
    <div class="mt-2 bg-light rounded shadow-sm p-2">
        <h6>
            {{ ++$index }}. Thiết lập cài đặt
        </h6>
        @php
            $childSettings = $settings->where('parent_id', '!=', null);
        @endphp
        @foreach ($settings as $setting)
            @if (empty($setting->parent_id))
                @include('admin.event_settings._setting', [
                    'event'     => $event,
                    'setting'   => $setting,
                ])
                @foreach ($childSettings as $childSetting)
                    @if ($childSetting->parent_id == $setting->id)
                        @include('admin.event_settings._setting', [
                            'event'     => $event,
                            'setting'   => $childSetting,
                            'isChild'   => true,
                        ])
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
@endsection

@push('admin_js')
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"> --}}

    {{-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css"> --}}
    {{-- <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script> --}}

    @vite([
        'resources/js/admin/checkins/config.js'
    ])

    <script>
        $(document).ready(function() {
            // const $container = $('.background-container');
            // const $draggable = $('.draggable-text-box');

            // $draggable.draggable({
            //     containment: $container,
            //     drag: function(event, ui) {
            //         const containerWidth = $container.width();
            //         const containerHeight = $container.height();
            //         const draggablePosition = ui.position;
            //         const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
            //         const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

            //         let pos_x = $(this).data('target-pos_x');
            //         let pos_y = $(this).data('target-pos_y');
            //         const $inputLeft = $(pos_x);
            //         const $inputTop = $(pos_y);

            //         $inputLeft.val(percentageLeft.toFixed(2));
            //         $inputTop.val(percentageTop.toFixed(2));
            //     },
            //     stop: function(event, ui) {
            //         const containerWidth = $container.width();
            //         const containerHeight = $container.height();
            //         const draggablePosition = ui.position;
            //         const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
            //         const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

            //         let pos_x = $(this).data('target-pos_x');
            //         let pos_y = $(this).data('target-pos_y');
            //         const $inputLeft = $(pos_x);
            //         const $inputTop = $(pos_y);

            //         $inputLeft.val(percentageLeft.toFixed(2));
            //         $inputTop.val(percentageTop.toFixed(2));
            //         console.log('Stopped dragging - Left:', percentageLeft.toFixed(2) + '%', 'Top:', percentageTop.toFixed(2) + '%');
            //     }
            // });

            // const initialDraggablePosition = $draggable.position();
            // const initialContainerWidth = $container.width();
            // const initialContainerHeight = $container.height();

            // if (initialContainerWidth > 0 && initialContainerHeight > 0) {
            //     const initialPercentageLeft = (initialDraggablePosition.left / initialContainerWidth) * 100;
            //     const initialPercentageTop = (initialDraggablePosition.top / initialContainerHeight) * 100;
            //     $inputLeft.val(initialPercentageLeft.toFixed(2));
            //     $inputTop.val(initialPercentageTop.toFixed(2));
            //     console.log('Initial Position - Left:', initialPercentageLeft.toFixed(2) + '%', 'Top:', initialPercentageTop.toFixed(2) + '%');
            // }
        });
    </script>
@endpush
