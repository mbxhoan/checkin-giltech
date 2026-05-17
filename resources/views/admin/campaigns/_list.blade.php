<div class="collapse p-2 bg-light rounded shadow-sm" id="collapseCampaigns">
    <div class="row mb-2">
        <div class="col-md-1 fw-bold text-sm text-center">
            #
        </div>
        <div class="col-md-4 fw-bold text-sm">
            Thông tin
        </div>
        <div class="col-md-6 fw-bold text-sm">

        </div>
    </div>

    @if (!empty($campaigns) && $campaigns->count())
        @foreach ($campaigns as $index => $campaign)
            <form action=""
                id="campaign-{{ $campaign->id }}"
                class="mb-2 pb-2 row pt-2 {{ $campaign->is_default ? "bg-light" : "" }}"
                method="POST"
            >
                @method('PUT')
                @csrf

                @include('components.form-groups.input-group', [
                    'id'                => "custom-field-template-{$campaign->id}",
                    'fieldName'         => "event_id",
                    'value'             => $event->id,
                    'type'              => "hidden",
                    'formClass'         => 'd-none',
                ])

                <div class="fw-bold mb-2 col-md-1 text-sm text-center">
                    {{ ++$index }}
                </div>

                <div class="col-md-4 text-sm">
                    <a class="fst-italic" target="_blank" href="{{ route('admin.campaigns.edit', [
                            'campaign'  => $campaign,
                        ]) }}"
                        id="lp-link-{{ $campaign->id }}"
                    >
                        {{ $campaign->code }}
                    </a>
                </div>

                <div class="col-md-4">
                    @include('components.select', [
                        'fieldName'     => 'status',
                        'id'            => 'status',
                        'options'       => $campaign->getStatues(),
                        'selected'      => $campaign->status,
                        'formClass'     => 'text-sm w-100',
                    ])
                </div>

                <div class="col-md-3 text-end">
                    @if (!$campaign->is_default)
                        {{-- <a href="" id="{{ $campaign->id }}"
                            class="btn btn-xs btn-danger btn-del-template"
                            data-id="custom-field-template-{{ $campaign->id }}"
                            data-url="{{ route('admin.custom_field_templates.destroy', [
                                'custom_field_template' => $campaign
                            ]) }}"
                        >
                            <x-icon name="trash" />
                        </a> --}}
                    @endif
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
            <a href="{{ route('admin.campaigns.create', $event) }}" class="btn btn-xs btn-primary w-100">
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm mới
            </a>
        </div>
    </div>
</div>

