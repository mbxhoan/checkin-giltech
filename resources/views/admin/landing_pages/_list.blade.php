<div class="collapse p-2 bg-light rounded shadow-sm" id="collapseLandingPages">
    <div class="row mb-2">
        <div class="col-md-1 fw-bold text-sm text-center">
            #
        </div>
        <div class="col-md-4 fw-bold text-sm">
            slug
        </div>
        <div class="col-md-6 fw-bold text-sm">

        </div>
    </div>

    @if (!empty($landingPages) && $landingPages->count())
        @foreach ($landingPages as $index => $landingPage)
            <form action="{{ route('admin.custom_field_templates.update', [
                    'custom_field_template' => $landingPage
                ]) }}"
                id="landing-page-{{ $landingPage->id }}"
                class="row mb-1 py-1 {{ $landingPage->is_default ? "bg-light" : "" }}"
                method="POST"
            >
                @method('PUT')
                @csrf

                @include('components.form-groups.input-group', [
                    'id'                => "custom-field-template-{$landingPage->id}",
                    'fieldName'         => "event_id",
                    'value'             => $event->id,
                    'type'              => "hidden",
                    'formClass'         => 'd-none',
                ])

                <div class="fw-bold mb-2 col-md-1 text-sm text-center">
                    {{ ++$index }}
                </div>

                <div class="col-md-6 text-sm">
                    <a class="fst-italic" target="_blank" href="{{ $landingPage->getRegisterUrl() }}" id="lp-link-{{ $landingPage->id }}">
                        {{ $landingPage->getRegisterUrl() }}
                    </a>
                </div>

                <div class="col-md-2">
                    @include('components.select', [
                        'fieldName'     => 'status',
                        'id'            => 'status',
                        'options'       => $landingPage->getStatues(),
                        'selected'      => $landingPage->status,
                        'formClass'     => 'text-sm w-100',
                    ])
                </div>

                <div class="col-md-3 text-end">
                    {{-- <button type="submit" class="btn btn-xs btn-primary">
                        <x-icon name="save" />
                        Lưu
                    </button> --}}

                    <button type="button" class="input-group-text btn btn-xs btn-primary" data-clipboard-target="#lp-link-{{ $landingPage->id }}">
                        <x-icon name="clipboard" prefix="fa-regular" />
                    </button>

                    <a href="{{ route('admin.landing_pages.edit', [
                            'event'         => $landingPage->event,
                            'landing_page'  => $landingPage,
                        ]) }}" target="_blank"
                        class="btn btn-xs btn-primary"
                    >
                        <x-icon name="edit" />
                    </a>

                    <a href="" id="{{ $landingPage->id }}"
                        class="btn btn-xs btn-danger btn-del-template"
                        data-id="landing-page-{{ $landingPage->id }}"
                        data-url="{{ route('admin.landing_pages.destroy', [
                            'landing_page' => $landingPage
                        ]) }}">
                        <x-icon name="trash" />
                    </a>
                </div>
            </form>
        @endforeach
    @else
        <div class="fst-italic text-sm mb-2">
            Chưa có
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.landing_pages.create', $event) }}" class="btn btn-xs btn-primary w-100">
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm mới
            </a>
        </div>
    </div>
</div>

