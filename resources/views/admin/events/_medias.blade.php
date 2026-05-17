<div class="collapse bg-light rounded shadow-sm p-2" id="collapseMedias">
    <div class="text-sm">
        <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'        => "main_bg_desktop",
                    'label'     => '1. Checkin '.'<i class="fa-solid fa-desktop"></i>',
                    'model'     => $event,
                    'type'      => "file",
                    'accept'    => ".png, .jpg, .jpeg",
                    'formClass' => 'mb-2 col-md-3'
                ])

                @include('components.form-groups.input-group', [
                    'id'        => "main_bg_mobile",
                    'label'     => '2. Checkin '.'<i class="fa-solid fa-mobile-screen"></i>',
                    'model'     => $event,
                    'type'      => "file",
                    'accept'    => ".png, .jpg, .jpeg",
                    'formClass' => 'mb-2 col-md-3'
                ])

                @include('components.form-groups.input-group', [
                    'id'        => "logo",
                    'label'     => '3. Logo '.'<i class="fa-solid fa-image"></i>',
                    'model'     => $event,
                    'type'      => "file",
                    'accept'    => ".png, .jpg, .jpeg",
                    'formClass' => 'mb-2 col-md-3'
                ])

                @include('components.form-groups.input-group', [
                    'id'        => "favicon",
                    'label'     => '4. Favicon '.'<i class="fa-solid fa-image"></i>',
                    'model'     => $event,
                    'type'      => "file",
                    'accept'    => ".png, .jpg, .jpeg",
                    'formClass' => 'mb-2 col-md-3'
                ])
            </div>

            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    @if ($event->main_bg_desktop && is_numeric($event->main_bg_desktop))
                        <div class="w-100">
                            <a href="{{ !empty($event->mainBgDesktop) ? $event->mainBgDesktop->getUrl() : null }}" class="w-100" target="_blank">
                                <img src="{{ !empty($event->mainBgDesktop) ? $event->mainBgDesktop->getUrl() : null }}" alt="{{ $event->mainBgDesktop ? $event->mainBgDesktop->name : null }}" width="100">
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
                        <div class="w-100">
                            <a href="{{ !empty($event->mainBgMobile) ? $event->mainBgMobile->getUrl() : null }}" class="w-100" target="_blank">
                                <img src="{{ !empty($event->mainBgMobile) ? $event->mainBgMobile->getUrl() : null }}" alt="{{ $event->mainBgMobile ? $event->mainBgMobile->name : null }}" width="100">
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->logo && is_numeric($event->logo))
                        <div class="w-100">
                            <a href="{{ !empty($event->logoUrl) ? $event->logoUrl->getUrl() : null ?? null }}" class="w-100" target="_blank">
                                <img src="{{ !empty($event->logoUrl) ? $event->logoUrl->getUrl() : null ?? null }}" alt="{{ $event->logoUrl ? $event->logoUrl->name : null }}" width="100">
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->faviconUrl)
                        <div class="w-100">
                            <a href="{{ !empty($event->faviconUrl) ? $event->faviconUrl->getUrl() : null }}" class="w-100" target="_blank">
                                <img src="{{ !empty($event->faviconUrl) ? $event->faviconUrl->getUrl() : null }}" alt="{{ $event->faviconUrl ? $event->faviconUrl->name : null }}" width="100">
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    @if ($event->mainBgDesktop)
                        <div class="w-100 mt-2">
                            <a href="{{ !empty($event->mainBgDesktop) ? $event->mainBgDesktop->getUrl() : null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>

                            <a href="{{ route('admin.media.show', $event->mainBgDesktop) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                <x-icon name="download" />
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
                        <div class="w-100 mt-2">
                            <a href="{{ !empty($event->mainBgMobile) ? $event->mainBgMobile->getUrl() : null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>

                            <a href="{{ route('admin.media.show', $event->mainBgMobile) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                <x-icon name="download" />
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->logoUrl)
                        <div class="w-100 mt-2">
                            <a href="{{ !empty($event->logoUrl) ? $event->logoUrl->getUrl() : null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>

                            <a href="{{ route('admin.media.show', $event->logoUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                <x-icon name="download" />
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    @if ($event->faviconUrl)
                        <div class="w-100 mt-2">
                            <a href="{{ !empty($event->faviconUrl) ? $event->faviconUrl->getUrl() : null }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>

                            <a href="{{ route('admin.media.show', $event->faviconUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                <x-icon name="download" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mt-2 justify-content-center">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-xs w-100">
                        <x-icon name="upload"/>
                        Cập nhật
                    </button>
                </div>
            </div>
        </form>

        <form action="{{ route('admin.event_files.upload', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row align-items-center mt-4 ">
                <div class="col-md-10">
                    @include('components.form-groups.input-group', [
                        'id'            => "medias",
                        'fieldName'     => "medias[]",
                        'label'         => '5. Nạp file ảnh tuỳ chọn (Nếu có) '.'<i class="fa-solid fa-images"></i>',
                        'model'         => $event,
                        'type'          => "file",
                        'accept'        => ".png, .jpg, .jpeg",
                        'formClass'     => 'mb-2 col-md-6 w-100',
                        'inputClass'    => 'form-control w-100',
                        'multiple'      => true,
                    ])
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-xs mt-2">
                        <x-icon name="upload"/>
                        Lưu
                    </button>
                </div>
            </div>
        </form>

        <div class="">
            @if ($eventFiles && $eventFiles->count())
                <div class="row px-2 mb-2 fw-bold">
                    <div class="col-md-2">
                        #
                    </div>

                    <div class="col-md-6">
                        Tên file
                    </div>

                    <div class="col-md-4">
                        Tuỳ chọn
                    </div>
                </div>

                @foreach ($eventFiles as $index => $eventFile)
                    <div class="row px-2 align-items-center mb-2">
                        <div class="col-md-2">
                            {{ ++$index}}
                        </div>

                        <div class="col-md-6">
                            @include('components.form-groups.input-group', [
                                'id'                => "event_file-".$eventFile->id,
                                'value'             => !empty($eventFile->media) ? $eventFile->media->getUrl() : null,
                                'type'              => "text",
                                'formClass'         => 'text',
                                'placeholder'       => "Link Qrcode",
                                'readonly'          => true,
                                'inputClass'        => 'form-control text-sm'
                            ])
                        </div>

                        <div class="col-md-4">
                            <a href="{{ !empty($eventFile->media) ? $eventFile->media->getUrl() : null }}" title="@lang('media.show')" class="btn btn-sm text-primary px-1" target="_blank">
                                <x-icon name="eye" prefix="fa-regular" />
                            </a>
                            <button type="button" class="text-primary btn btn-sm px-1" data-clipboard-target="#event_file-{{ $eventFile->id }}">
                                <x-icon name="clipboard" prefix="fa-regular" />
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="fw-bold fst-italic">
                    Chưa có file nào được nạp
                </div>
            @endif
        </div>
    </div>
</div>
