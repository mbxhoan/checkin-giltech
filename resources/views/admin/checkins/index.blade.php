
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Khách mời sự kiện {$event->code}"
])

@section('title')
    Lượt check in/out: <span class="text-danger">{{ $total ?? 0 }}</span>
@endsection

@section('buttons')
    <div class="buttons">
        @include('components.btn-copy', [
            'class'     => '',
            'targetId'  => "event_code-{$event->id}"
        ])
        <span class="fw-bold me-2" id="event_code-{{ $event->id }}">{{ $event->code }}</span>
        @admin
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="calendar-days"/>
                Sự kiện
            </a>
        @endadmin
    </div>
@endsection

@section('primary-content')
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm px-3 py-2">
            <x-icon name="arrow-circle-right" />
            <span class="fw-semibold">Tổng khách mời</span>
            <span class="badge bg-primary rounded-pill">{{ !empty($clients) ? $clients->count() : 0 }}</span>
        </a>
        
        <a href="{{ route('admin.checkins.index', ['event' => $event, 'type' => 'CHECKIN']) }}" class="btn btn-outline-success d-flex align-items-center gap-2 shadow-sm px-3 py-2">
            <span class="fw-semibold">Đã Check-in</span>
            <span class="badge bg-success rounded-pill">{{ $totalCheckedIn ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.checkins.index', ['event' => $event, 'type' => 'CHECKOUT']) }}" class="btn btn-outline-warning d-flex align-items-center gap-2 shadow-sm px-3 py-2">
            <span class="fw-semibold text-dark">Đã Check-out</span>
            <span class="badge bg-warning text-dark rounded-pill">{{ $totalCheckedOut ?? 0 }}</span>
        </a>
    </div>
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">
            <a href=""
                class="btn {{ request()->hasAny([
                    'customer_id',
                    'unique_qrcode',
                    'status',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'btn-outline-warning' : 'btn-warning' }}
                btn-sm align-self-center mb-lg-0 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
            >
                Bộ lọc
                <x-icon name="filter"/>
            </a>
            @include('admin.checkins._modal-filter', [
                'modalId'       => 'filterModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => $model,
                'route'         => route('admin.checkins.index', [
                    'event'     => $event
                ]),
            ])
            @include('admin.checkins._btn-export-list', [
                'event'     => $event,
                'fields'    => request()->all(),
                'route'     => route('admin.checkins.export-check-in-out', array_merge(
                    [
                    'event' => $event],
                    request()->all()
                )),
                'text'      => 'Chi tiết'
            ])
            @include('admin.checkins._btn-export-list', [
                'event'     => $event,
                'fields'    => request()->all(),
                'route'     => route('admin.checkins.export-checkin_count', [
                    'event' => $event
                ]),
                'text'      => 'Số lần checkin'
            ])
            @admin
                <button type="button" class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2" data-bs-toggle="modal" data-bs-target="#confirmResetModal">
                    <x-icon name="eraser"/>
                    Reset
                </button>
                <!-- Modal Xác nhận Reset -->
                <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmResetModalLabel">Xác nhận reset danh sách</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="text-sm py-2 px-3">
                                Vui lòng backup lại dữ liệu trước khi reset danh sách:
                                @include('admin.checkins._btn-export-list', [
                                    'event'     => $event,
                                    'fields'    => request()->all(),
                                    'route'     => route('admin.checkins.export-check-in-out', [
                                        'event' => $event
                                    ]),
                                ])
                            </div>
                            <form method="POST" action="{{ route('admin.checkins.destroy-all', [
                                    'event' => $event
                                ]) }}?{{ http_build_query(request()->all()) }}" class="d-inline"
                            >
                                @csrf
                                @method('DELETE')
                                <div class="modal-body">
                                    <p>
                                        Bạn có chắc chắn muốn reset tất cả khách mời đang hiển thị theo bộ lọc?
                                    </p>
                                    <div class="row my-2">
                                        @include('components.form-groups.input-group', [
                                            'id'                => "confirm",
                                            'fieldName'         => "confirm",
                                            'value'             => '',
                                            'label'             => 'VUI LÒNG NHẬP <b>"DELETE"</b> ĐỂ XÁC NHẬN XOÁ',
                                            'type'              => "text",
                                            'formClass'         => 'mb-3 col-md-12',
                                        ])
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                                    <button type="submit" class="btn btn-danger">Xác nhận reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endadmin
        </div>
    </div>
    <div class="table-responsive pb-2">
        {!! $dataTable->table() !!}
    </div>
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}
@endpush
