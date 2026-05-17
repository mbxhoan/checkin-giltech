<form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                @if ($screen === "desktop")
                    @include('components.form-groups.input-group', [
                        'id'        => "main_bg_desktop",
                        'label'     => '1. Checkin '.'<i class="fa-solid fa-desktop"></i>',
                        'model'     => $event,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2 col-md-12'
                    ])
                    <div class="col-md-12 text-center">
                        @if ($event->main_bg_desktop && is_numeric($event->main_bg_desktop))
                            <div class="w-100">
                                <a href="{{ $event->mainBgDesktop->getUrl() ?? null }}" class="w-100" target="_blank">
                                    <img src="{{ $event->mainBgDesktop->getUrl() ?? null }}" alt="{{ $event->mainBgDesktop->name }}" width="100">
                                </a>
                            </div>
                            <div class="w-100 mt-2">
                                <a href="{{ $event->mainBgDesktop->getUrl() ?? null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                    <x-icon name="eye" prefix="fa-regular" />
                                </a>
                                <a href="{{ route('admin.media.show', $event->mainBgDesktop) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                    <x-icon name="download" />
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    @include('components.form-groups.input-group', [
                        'id'        => "main_bg_mobile",
                        'label'     => '1. Checkin '.'<i class="fa-solid fa-mobile-screen"></i>',
                        'model'     => $event,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2 col-md-12'
                    ])
                    <div class="col-md-12 text-center">
                        @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
                            <div class="w-100">
                                <a href="{{ $event->mainBgMobile->getUrl() ?? null }}" class="w-100" target="_blank">
                                    <img src="{{ $event->mainBgMobile->getUrl() ?? null }}" alt="{{ $event->mainBgMobile->name }}" width="100">
                                </a>
                            </div>
                            <div class="w-100 mt-2">
                                <a href="{{ $event->mainBgMobile->getUrl() ?? null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                    <x-icon name="eye" prefix="fa-regular" />
                                </a>
                                <a href="{{ route('admin.media.show', $event->mainBgMobile) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                    <x-icon name="download" />
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                @if ($event->sound_success)
                    <audio id="sound_success-{{ $event->id }}" src="{{ asset("storage/{$event->sound_success}") }}"></audio>
                @endif
                @include('components.form-groups.input-group', [
                    'id'        => "sound",
                    'label'     => '<i class="fa fa-volume-up"></i> Thành công'.
                        ($event->sound_success ? ' <a href="" class="audio-play" data-id="sound_success-'.$event->id.'">▶</a>' : '').
                        /* offcanvas upload audio */
                        ' <a class="text text-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
                            <i class="fa-solid fa-rocket fa-bounce"></i>
                        </a>',
                    'model'     => null,
                    'type'      => "hidden",
                    'accept'    => "audio/*",
                    'formClass' => 'mb-2 col-md-12'
                ])
            </div>
            <div class="row">
                @if ($event->sound_fail)
                    <audio id="sound_fail-{{ $event->id }}" src="{{ asset("storage/{$event->sound_fail}") }}"></audio>
                @endif
                @include('components.form-groups.input-group', [
                    'id'        => "sound",
                    'label'     => '<i class="fa fa-volume-up"></i> Thất bại'.
                        ($event->sound_fail ? ' <a href="" class="audio-play text-danger" data-id="sound_fail-'.$event->id.'">▶</a>' : '').
                        /* offcanvas upload audio */
                        ' <a class="text text-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
                            <i class="fa-solid fa-rocket fa-bounce"></i>
                        </a>',
                    'model'     => $event,
                    'type'      => "hidden",
                    'accept'    => "audio/*",
                    'formClass' => 'mb-2 col-md-12'
                ])
            </div>
        </div>
    </div>
    <div class="row mt-2 justify-content-center">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <x-icon name="upload"/>
                Cập nhật
            </button>
        </div>
    </div>
</form>

{{-- offcanvas --}}
<div class="offcanvas offcanvas-end" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Tạo mới Audio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        {{-- default --}}
        <div class="row">
            <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data"
                class="col-md-6"
            >
                @csrf
                <div class="row align-items-center mb-2">
                    @include('components.form-groups.input-group', [
                        'id'        => "sound_success",
                        'label'     => '<i class="fa fa-volume-up"></i> Thành công'.
                            ($event->sound_success ? ' <a href="" class="audio-play" data-id="sound_success-'.$event->id.'">▶</a>' : ''),
                        'model'     => $event,
                        'type'      => "file",
                        'accept'    => "audio/*",
                        'formClass' => 'mb-2 col-md-12'
                    ])
                </div>
            </form>
            <form action="{{ route('admin.audios.set-to-event', $event) }}" class="col-md-6">
                @csrf
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2">
                        @include('components.select', [
                            'label'         => 'Audio',
                            'id'            => 'sound_success',
                            'fieldName'     => 'sound_success',
                            'formClass'     => 'text-sm w-100',
                            'options'       => ["" => "-"] + (!empty(auth()->user()->company->audios) ? auth()->user()->company->audios
                                ->pluck('text', 'file_path')
                                ->toArray() : []),
                            'selected'      => $event->sound_success,
                        ])
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-xs btn-primary mt-4">
                            Chọn
                        </button>
                    </div>
               </div>
            </form>
        </div>
        <div class="row">
            @if ($event->sound_success)
                <div class="col-md-9">
                    <audio controls>
                        <source src="{{ asset("storage/{$event->sound_success}") }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <audio id="sound_success-{{ $event->id }}" src="{{ asset("storage/{$event->sound_success}") }}"></audio>
            @endif
        </div>

        <div class="row">
            <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data"
                class="col-md-6"
            >
                @csrf
                <div class="row align-items-center mb-2">
                    @include('components.form-groups.input-group', [
                        'id'        => "sound_fail",
                        'label'     => '<i class="fa fa-volume-up"></i> Thất bại'.
                            ($event->sound_fail ? ' <a href="" class="audio-play text-danger" data-id="sound_fail-'.$event->id.'">▶</a>' : ''),
                        'model'     => $event,
                        'type'      => "file",
                        'accept'    => "audio/*",
                        'formClass' => 'mb-2 col-md-12'
                    ])
                </div>
            </form>
            <form action="{{ route('admin.audios.set-to-event', $event) }}" class="col-md-6">
                @csrf
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2">
                        @include('components.select', [
                            'label'         => 'Audio',
                            'id'            => 'sound_fail',
                            'fieldName'     => 'sound_fail',
                            'formClass'     => 'text-sm w-100',
                            'options'       => ["" => "-"] + (!empty(auth()->user()->company->audios) ? auth()->user()->company->audios
                                ->pluck('text', 'file_path')
                                ->toArray() : []),
                            'selected'      => $event->sound_fail,
                        ])
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-xs btn-primary mt-4">
                            Chọn
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            @if ($event->sound_fail)
                <div class="col-md-9">
                    <audio controls>
                        <source src="{{ asset("storage/{$event->sound_fail}") }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <div class="col-md-3">

                </div>
                <audio id="sound_fail-{{ $event->id }}" src="{{ asset("storage/{$event->sound_fail}") }}"></audio>
            @endif
        </div>
        <div class="bg-light p-2 rounded shadow-sm mt-3">
            {{-- update --}}
            @if (!empty(auth()->user()->company->audios) && auth()->user()->company->audios->count())
                <h5>
                    Audio đã lưu
                </h5>
                <div class="mb-2">
                    @foreach (auth()->user()->company->audios as $audio)
                        <form action="{{ route('admin.audios.update', $audio) }}" method="POST">
                            @csrf
                            @method("PUT")
                            <div class="row align-items-center">
                                @include('components.form-groups.input-group', [
                                    'id'            => "text",
                                    'model'         => $audio,
                                    'placeholder'   => "Text",
                                    'type'          => "text",
                                    'inputClass'    => 'form-control text-sm w-100',
                                    'formClass'     => 'mb-2 col-md-6'
                                ])
                                <div class="col-md-3 mb-2">
                                    @include('components.select', [
                                        'id'            => 'voice',
                                        'fieldName'     => 'voice',
                                        'formClass'     => 'text-sm',
                                        'options'       => $audio->getAITTtsModel(),
                                        'selected'      => $audio->voice,
                                    ])
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-xs btn-primary mb-1 btn-submit-form">
                                        Cập nhật
                                    </button>
                                    {{-- @if ($audio->file_path)
                                        <a href="" class="audio-play text-danger mb-1" data-id="sound-{{ $audio->id }}">▶</a>
                                        <audio id="sound-{{ $audio->id }}" class="" src="{{ asset("storage/{$audio->file_path}") }}"></audio>
                                    @endif --}}
                                </div>
                            </div>
                            @if ($audio->file_path)
                                <div class="row">
                                    <div class="col-md-9">
                                        <audio controls>
                                            <source src="{{ asset("storage/{$audio->file_path}") }}" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                    <div class="col-md-3">

                                    </div>
                                </div>
                            @endif
                        </form>
                    @endforeach
                </div>
            @endif
            {{-- create --}}
            <form action="{{ route('admin.audios.store') }}" method="POST">
                @csrf
                <div class="row">
                    @include('components.form-groups.input-group', [
                        'id'            => "text",
                        'label'         => "Tạo audio với AI",
                        'labelClass'    => 'form-label text-sm',
                        'placeholder'   => "Chúc mừng bạn đã checkin thành công",
                        'type'          => "text",
                        'inputClass'    => 'form-control text-sm',
                        'formClass'     => 'mb-2 col-md-6'
                    ])
                    <div class="col-md-3 mb-2">
                        @include('components.select', [
                            'labelClass'    => 'form-label text-sm',
                            'label'         => "Giọng nói",
                            'id'            => 'voice',
                            'fieldName'     => 'voice',
                            'formClass'     => 'text-sm',
                            'options'       => $audio->getAITTtsModel(),
                            'selected'      => null,
                        ])
                    </div>
                    <div class="col-md-3 pt-4">
                        <button type="submit" class="btn btn-xs btn-primary btn-submit-form mt-2">
                            Lưu
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
