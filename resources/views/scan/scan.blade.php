@php
    $isOpenCamera = $event->getEventSetting("ALLOW_CHECKIN_CAMERA", strtoupper($screen))->value ?? null;
@endphp

@extends('scan.layouts.templates.page-full', [
    'pageTitle'         => "Checkin",
    'favicon'           => null,
    'popErrors'         => true,
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "Checkin",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ?? config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="" id="background">
        {{-- customize --}}
        {{-- galaxy-holding --}}
        @if ($event->code == "galaxy-holding" && $agent->isMobile())
            <div class="text-center text-xs" style="position: absolute; color: rgba(255, 0, 0, 0.886); font-weight: bold; left: 50%; bottom: 0%; transform: translateX(-50%);">
                {{ auth()->user()->name }}
            </div>
        @endif
        {{-- overlay layer --}}
        <div class="overlay-layer display-area bg-light">
            <div class="loading-filter">
                <i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading
            </div>
        </div>

        {{-- input --}}
        <input type="hidden" class="form-control d-none" id="event_code" name="event_code" value="{{ $event->code }}">
        {{-- <input type="text" class="form-control" id="qrcode" name="qrcode" value="" readonly autofocus autocomplete="off" contenteditable="true"> --}}
        <input
                type="text"
                class="form-control"
                id="qrcode"
                name="qrcode"
                value=""
                readonly
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                inputmode="none"
                virtualkeyboardpolicy="manual"
            >

        {{-- custom fields --}}
        @foreach ($customFieldTemplates as $customFieldTemplate)
            <div id="field-{{ $customFieldTemplate->name }}"
                class="
                    {{-- custom-field-box --}}
                    {{ $customFieldTemplate->type == $customFieldTemplate::TYPE_TEXT_FIX ? "show-fix-text" : "custom-field-box" }}
                    {{ $customFieldTemplate->show_prefix ? "show-prefix" : "" }}
                    {{ $customFieldTemplate->type == $customFieldTemplate::TYPE_IMAGE ? "show-image-link" : "" }}
                "
                data-prefix="{{ $customFieldTemplate->show_prefix ? ($customFieldTemplate->description ?? $customFieldTemplate->name).":" : "" }}"
                {{-- style="{{ $customFieldTemplate->type == $customFieldTemplate::TYPE_TEXT_FIX ? "display: block !important;" : "" }}" --}}
            >
                {{-- {{ $customFieldTemplate->description ?? $customFieldTemplate->name }} --}}
                @if ($customFieldTemplate->show_prefix)
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}:
                @else
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}
                @endif
            </div>
            {{-- customize --}}
            {{-- aura --}}
            @if ($event->code == "aura")
                <style>
                    @font-face {
                        font-family: 'Viction';
                        src: url('{{ asset('assets/fonts/VictionDisplay-Regular.ttf') }}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                    }
                    .custom-field-box,
                    .show-fix-text,
                    .show-prefix {
                        font-family: 'Viction', sans-serif !important;
                    }
                </style>
            @endif
            {!! $customFieldTemplate->generateCssFromAttributes($customFieldTemplate->checkins ?? [], "field-{$customFieldTemplate->name}", $screen) !!}
        @endforeach

        {{-- custom messages --}}
        @foreach ($customCheckinMessages as $msg => $customCheckinMessageAttr)
            <div id="msg-{{ $msg }}" class="custom-message {{ isset($customCheckinMessageAttr['link']) ? "show-image" : "" }}">
                {{-- {{ $customCheckinMessageAttr['msg'] ?? null }} --}}
                {{-- show image --}}
                @if (isset($customCheckinMessageAttr['link']))
                    <img
                        src="{{ $customCheckinMessageAttr['link'] }}"
                        width="100%"
                        alt="{{ $customCheckinMessageAttr['msg'] }}"
                        loading="lazy"
                    >
                @else
                    {{ $customCheckinMessageAttr['msg'] }}
                @endif
            </div>
            {{-- customize --}}
            {{-- aura --}}
            @if ($event->code == "aura")
                <style>
                    @font-face {
                        font-family: 'Viction';
                        src: url('{{ asset('assets/fonts/VictionDisplay-Regular.ttf') }}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                    }
                    .custom-message {
                        font-family: 'Viction', sans-serif !important;
                    }
                </style>
            @endif
            {!! $event->generateCssFromAttributes($customCheckinMessages ?? [], "msg-{$msg}", $msg) !!}
        @endforeach

        {{-- sound --}}
        @if ($event->getEventSetting("ALLOW_CHECKIN_PLAYING_SOUND", strtoupper($screen))->value ?? null)
            @if ($event->sound_success)
                <audio id="sound_success" src="{{ asset("storage/{$event->sound_success}") }}"></audio>
            @endif
            @if ($event->sound_fail)
                <audio id="sound_fail" src="{{ asset("storage/{$event->sound_fail}") }}"></audio>
            @endif
        @endif

        {{-- camera --}}
        @if ($isOpenCamera)
            <div id="cameraBtns" class="{{ $screen == "desktop" ? 'w-50 p-4' : 'w-90 p-2' }} bg-white rounded" style="display: none;">
                <div id="camera-qrcode-reader" data-placeholder="{{ asset('assets/images/placeholders/camera.png') }}">

                </div>
                <div id="camera-placeholder">
                    <img src="{{ asset('assets/images/placeholders/camera.png') }}" alt="" width="100%" loading="lazy">
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-sm btn-primary" id="cameraBtn" title="Mở camera">
                        <x-icon name="camera"/>
                        Mở camera
                    </a>
                    <a href="#" class="btn btn-xs btn-danger" id="stopBtn" style="display: none;">
                        <x-icon name="circle-xmark"/>
                    </a>
                </div>
            </div>
        @endif

        {{-- buttons block --}}
        <div class="" id="btn-blocks">
            <a
                href="{{ route('scan.index') }}"
                class="text-xs"
                title="Trở về"
            >
                <x-icon name="arrow-left" />
            </a>
            <a
                href=""
                class="text-xs ms-2"
                title="Hiển thị trường thông tin"
                id="btn-show-fields"
            >
                <x-icon name="eye" prefix="fa-regular" />
            </a>
            <a
                href=""
                class="text-xs text-success ms-2"
                title="Hiển thị thông báo"
                id="btn-show-messages"
            >
                <x-icon name="eye" prefix="fa-regular" />
            </a>
            <a
                href=""
                class="text-xs text-danger ms-2"
                title="Hiển thị textbox"
                id="btn-show-input"
            >
                <x-icon name="edit"/>
            </a>
        </div>
        {{-- customize --}}
        {{-- cbre-1301 --}}
        {{-- the-nest --}}
        {{-- cadivi-2026 --}}
        @if ($isOpenCamera)
            @if (in_array($event->code, [
                "cbre-1301",
                "the-nest",
                "cadivi-2026",
            ]))
                <div id="btn-show-camera-lg">
                    <a href=""
                        class="text-xs ms-2"
                        title="Bật/Tắt camera"
                        id="btn-show-camera"
                        style="font-size: 5rem;"
                    >
                        <x-icon name="camera"/>
                    </a>
                </div>

            @else
                <a href=""
                    class="text-xs ms-2"
                    title="Bật/Tắt camera"
                    id="btn-show-camera"
                >
                    <x-icon name="camera"/>
                </a>
            @endif
            @push('scan_js')
                <script src="{{ asset('offlines/offline-js/html5-qrcode.min.js') }}"></script>
            @endpush
        @endif
    </div>
    @if (!empty($label) && !$agent->isMobile())
        <div>
            @if ($event && !empty($clients))
                {{-- @include('components.label_details._multi-print', [
                    'label'             => $label,
                    'labelDetails'      => $label->label_details->where('status', '!=', "DELETED") ?? null,
                    'clients'           => $clients,
                ]) --}}
            @endif
        </div>
        <div id="printContainer" class="d-none"></div>
        <input type="hidden" name="" id="label_id" value="{{ $label->id }}">
        <input type="hidden" name="" id="url" value="{{ route('scan.render-label', [
                'label' => $label
            ]) }}"
        >
    @endif

    {{-- offcanvas --}}
    @include('scan.offline._offcanvas-offline')
@endsection

@push('scan_js')
    @vite([
        'resources/js/scan/scan.js'
    ])
    @include('scan.offline._fetch-clients', [
        'clients' => $clients,
    ])
    <script>
        function scanQrcode(code) {
            console.log("Scanned code:", code);

            // 👉 Post to server
            fetch('/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    code: code
                }),
            })
            .then(response => response.json())
            .then(data => console.log('Server response:', data))
            .catch(error => console.error('Error:', error));
        }

        function inputQrcodeByChange() {
            $(document).on("change", function(e) {
                let qrcode = $(event.target).val(); // Get the value of the input currently focused
                console.log(qrcode);
                scanQrcode(qrcode);
                $(event.target).val('');
            });
        };

        function inputQrcodeByKeyUp() {
            $(document).on("keyup", function(e) {
                const keyCode = e.code || e.keyCode;

                if (keyCode == 13) {
                    navigator.clipboard
                    .readText()
                    .then(
                        (clipText) => {
                            let qrcode = clipText.trim();
                            console.log(qrcode);
                            // scanQrcode(qrcode);
                            $('input#qrcode').val('');
                        }
                    );
                }
            });
        };

        function inputQrcodeByKeyDown() {
            $(document).on("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    let qrcode = $(event.target).val(); // Get the value of the input currently focused
                    console.log(qrcode);
                    // scanQrcode(qrcode);
                    $(event.target).val('');
                }
            });
        }

        // 2. Listen for clipboard paste event
        function inputQrcodeByClipboard() {
            document.addEventListener('paste', function (e) {
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                if (pastedData) {
                    // scanQrcode(pastedData.trim());
                    console.log(pastedData.trim());
                }
            });
        }

        // 3. Listen for broadcast event (assuming the reader triggers custom JS event)
        window.addEventListener('barcode-scanned', function (e) {
            if (e.detail && e.detail.code) {
                console.log(e.detail.code);
                // scanQrcode(qrcode);
                $(event.target).val('');
            }
        });
    </script>
@endpush

@push('scan_css')
    <style>
        .swal2-actions {
            width: 85%;
            display: flex;
            justify-content: space-between;
        }
        #background {
            width: 100vw !important;
            height: 100vh;
            max-height: 100vh;
            position: relative;
            overflow: hidden;
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        .custom-field-box {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
            display: none;
            white-space: pre-line;
        }
        .show-fix-text {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
        }
        .custom-message {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
            display: none;
        }
        #cameraBtns {
            position: absolute; /* Allows dragging relative to the parent */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -55%); /* Perfect centering */
            z-index: 10;
        }
        #btn-blocks {
            position: absolute; /* Allows dragging relative to the parent */
            bottom: 0%;
            left: 1%;
            z-index: 10;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        /* customize */
        /* cbre-1301 */
        /* cadivi-2026 */
        #btn-show-camera-lg {
            position: absolute; /* Allows dragging relative to the parent */
            bottom: 0%;
            left: 50%;
            z-index: 10;
            font-size: 5rem;
            transform: translate(-50%, 0%);
        }
        #camera-qrcode-reader,
        #camera-placeholder {
            /* width: 270px; */
            /* display: none; */
            /* aspect-ratio: 1 / 1; */
            {{ $screen == "desktop" ? "width: 75%;" : 'width: calc(100vw - 50px);' }}
            {{ $screen == "desktop" ? "margin: auto;" : '' }}
            position: relative;
            text-align: center;
        }
        #camera-placeholder {
            /* display: none; */
        }
        #qrcode {
            opacity: 0;
        }
        .overlay-layer {
            display: none;
            width: 100vw;
            height: 100vh;
            position: absolute;
            background-color: #dcdcdc82 !important;
            z-index: 999;
        }
        .overlay-layer .loading-filter {
            /* background: url('/assets/img/loading-gear.gif'); */
            background-position: center;
            background-size: 100%;
            background-repeat: no-repeat;
            opacity: 100%;
            width: 100px;
            height: 100px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #b8b8b887;
        }
    </style>
@endpush
