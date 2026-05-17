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
        {{-- input --}}
        <input type="hidden" class="form-control d-none" id="event_code" name="event_code" value="{{ $event->code }}">
        <input type="text" class="form-control" id="qrcode" name="qrcode" value="" autofocus autocomplete="off" contenteditable="true">

        {{-- custom fields --}}
        @foreach ($customFieldTemplates as $customFieldTemplate)
            <div id="field-{{ $customFieldTemplate->name }}"
                class="custom-field-box
                    {{ $customFieldTemplate->show_prefix ? "show-prefix" : "" }}
                    {{ $customFieldTemplate->type == $customFieldTemplate::TYPE_IMAGE ? "show-image-link" : "" }}
                "
                data-prefix="{{ $customFieldTemplate->show_prefix ? ($customFieldTemplate->description ?? $customFieldTemplate->name).":" : "" }}"
            >
                {{-- {{ $customFieldTemplate->description ?? $customFieldTemplate->name }} --}}
                @if ($customFieldTemplate->show_prefix)
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}:
                @else
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}
                @endif
            </div>
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
                    >
                @else
                    {{ $customCheckinMessageAttr['msg'] }}
                @endif
            </div>
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
        <div id="cameraBtns" class="{{ $screen == "desktop" ? 'w-50 p-4' : 'w-90 p-2' }} bg-white rounded" style="display: none;">
            <div id="camera-qrcode-reader" data-placeholder="{{ asset('assets/images/placeholders/camera.png') }}">

            </div>
            <div id="camera-placeholder">
                <img src="{{ asset('assets/images/placeholders/camera.png') }}" alt="" width="100%">
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
            @if ($event->getEventSetting("ALLOW_CHECKIN_CAMERA", strtoupper($screen))->value ?? null)
                <a href=""
                    class="text-xs ms-2"
                    title="Bật/Tắt camera"
                    id="btn-show-camera"
                >
                    <x-icon name="camera"/>
                </a>
            @endif
        </div>
    </div>
    @if (!empty($label))
        <div id="printContainer" class="d-none"></div>
        <input type="hidden" name="" id="label_id" value="{{ $label->id }}">
        <input type="hidden" name="" id="url" value="{{ route('scan.render-label', [
                'label' => $label
            ]) }}"
        >
    @endif
@endsection

@push('scan_js')
    @include('scan.offline._fetch-clients', [
        'clients' => $clients,
    ])
    @include('scan.offline._checkin-offline')
    <script src="{{ asset('offlines/offline-js/html5-qrcode.min.js') }}"></script>
    @vite([
        'resources/js/scan/offline.js'
    ])
    <script>
        $(document).ready(function() {
            // inputQrcodeByChange();
            // inputQrcodeByClipboard();
            // inputQrcodeByKeyUp();
            // inputQrcodeByKeyDown();
        });

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

    {{-- MOBILE --}}
    <script>
        const input = document.getElementById('qrcode');
        input.removeAttribute('readonly');
        // input.focus();
        // input.setAttribute('readonly', true);

        window.onload = function () {
            document.getElementById('qrcode').focus();
        };
    </script>

    @if (!$agent->isMobile())
        <script>
            /* always focus */
            $(document).on("click", function(e) {
                $('input#qrcode').focus();
            });
        </script>
    @endif
@endpush

@push('scan_css')
    <style>
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
    </style>
@endpush
