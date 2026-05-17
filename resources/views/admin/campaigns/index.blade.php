
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Danh sách Campaigns"
])

@section('title')
    <div class="d-flex">
        <div class="bg-light border rounded shadown-sm px-4 py-3">
            <h5>
                Campaign(s):
            </h5>
            <div class="text-danger">
                {{ $total ?? 0 }}
            </div>
        </div>
        <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2">
            <h5 class="mb-0 pb-0">
                Đã gửi
            </h5>
            <div class="text-danger">
                <span class="text-lg">{{ $sentEmailCount ?? 0 }} </span>
                @if (!empty($limitedEmails))
                    <span class="text-xs text-secondary">/{{ $limitedEmails }}</span>
                    @include('components._progress', [
                        'completed'     => $sentEmailCount ?? 0,
                        'total'         => $limitedEmails ?? $sentEmailCount,
                        'width'         => 300,
                    ])
                @else
                    <span class="text-xs text-secondary">Không giới hạn</span>
                @endif
            </div>
        </div>
        @if (count($dataStatuses))
            @foreach ($dataStatuses as $status => $count)
                <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2" style="width: 150px;">
                    <h5>
                        {{ $status }}
                    </h5>
                    <div class="text-danger">
                        {{ $count }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.email_templates.index') }}" class="btn btn-primary btn-sm align-self-center mb-1 ms-1">
            <x-icon name="calendar-days"/>
            Templates
        </a>
        <a href="" class="btn btn-sm btn-primary align-self-center mb-1 ms-1"
            data-bs-toggle="modal"
            data-bs-target="#selectEventModal"
        >
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
        <div class="modal fade" id="selectEventModal" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="selectEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="selectEventModalLabel">
                            Chọn sự kiện
                            <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                                <x-icon name="plus-square" prefix="fa-regular"/>
                            </a>
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.campaigns.select-event-to-create') }}" method="GET">
                        <div class="modal-body text-sm">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('components.select', [
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => null,
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                @lang('common.close')
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Chọn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('primary-content')
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">
            {{-- <a href=""
                class="btn {{ request()->hasAny([
                    'customer_id',
                    'status',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'btn-outline-warning' : 'btn-warning' }}
                btn-sm align-self-center mb-lg-0 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#selectEventModal"
            >
                Bộ lọc
                <x-icon name="filter"/>
            </a> --}}
            @include('admin.campaigns._modal-filter', [
                'modalId'       => 'selectEventModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => \App\Models\Client::getModel(),
                'route'         => route('admin.campaigns.index'),
            ])
            {{-- <a href="{{ route('admin.campaigns.export-list', ['event' => $event]) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="file-excel" prefix="fa-solid"/>
                @lang('imports.export')
            </a> --}}
        </div>
    </div>

    {{ $dataTable->table() }}
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
