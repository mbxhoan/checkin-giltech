
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Khách mời sự kiện {$event->code}"
])

@section('title')
    Số lượng khách mời: <span class="text-danger">{{ $total ?? 0 }}</span>
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
            <a href="{{ route('admin.clients.import', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="upload"/>
                Nạp
            </a>
        @endadmin
        <a href="{{ route('admin.clients.create', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            @lang('forms.actions.add')
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="mb-3">
        <a href="{{ route('admin.checkins.index', $event) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center rounded gap-2 px-3 py-2 transition-all">
            <x-icon name="arrow-circle-right" />
            <span class="fw-medium">Đã Check-in & Check-out</span>
        </a>
    </div>
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">
            <a href=""
                class="btn {{ request()->hasAny([
                    'event_id',
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
            @include('admin.clients._modal-filter', [
                'event'         => $event,
                'modalId'       => 'filterModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => \App\Models\Client::getModel(),
                'route'         => route('admin.clients.index', [
                    'event'     => $event
                ]),
            ])
            @include('admin.clients._btn-export-list', [
                'event'         => $event,
                'fields'        => request()->all()
            ])
            <a href="{{ route('admin.clients.export-qrcodes', [
                    'event' => $event
                ]) }}?{{ http_build_query(request()->all()) }}" title="Tải xuống" class="btn btn-success btn-sm btn-get"
            >
                <x-icon name="file-excel" prefix="fa-solid"/>
                Qrcodes
            </a>
            {{-- customize --}}
            {{-- hidec-vn --}}
            @if ($event->code == "hidec-vn")
                <a href="{{ route('admin.clients.lucky-draw-list', $event) }}"
                    title="Tải xuống"
                    class="btn btn-success btn-sm btn-get"
                >
                    <x-icon name="file-excel" prefix="fa-solid"/>
                    DS Lucky Draw
                </a>
            @endif
            @if (is_numeric($total) && $total > 0)
                @if ($notHavingImgQrcodes == 0)
                    <a href="{{ route('admin.clients.download-qrcodes', [
                            'event' => $event
                        ]) }}?{{ http_build_query(request()->all()) }}" title="Tải xuống" class="btn btn-primary btn-sm btn-get"
                    >
                        <x-icon name="qrcode" />
                        Tải Qrcodes
                    </a>
                @endif
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
                                    @include('admin.clients._btn-export-list', [
                                        'event'     => $event,
                                        'fields'    => request()->all()
                                    ])
                                </div>
                                <form method="POST" action="{{ route('admin.clients.destroy-all', [
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
            @endif
        </div>
        <div class="d-flex">
            @if (!empty($clients) && !empty($label))
                <a href="" id="btn-multi-print" class="btn btn-danger btn-sm mx-2">
                    <x-icon name="print"></x-icon>
                    In toàn bộ
                </a>
                <div id="printContainer">
                    @include('components.label_details.to-print', [
                        'clients'           => $clients,
                        'label'             => $label,
                        'labelDetails'      => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                        'event'             => $event,
                        'client'            => $client ?? null,
                        'display'           => false,
                    ])
                </div>
            @endif
            @admin
                @if (is_numeric($total) && $total > 0)
                    <form action="{{ route('admin.clients.generate-qrcodes', [
                            'event' => $event
                        ]) }}?{{ http_build_query(request()->all()) }}"
                        method="POST"
                    >
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary btn-submit-form" title="Đang có {{ $total - $notHavingImgQrcodes }}/{{ $total }} hình mã Qrcodes">
                            <x-icon name="code" />
                            Chạy Qrcodes ({{ $total - $notHavingImgQrcodes }}/{{ $total }})
                        </button>
                    </form>
                @endif
            @endadmin
        </div>
    </div>
    <div class="table-responsive pb-2">
        {!! $dataTable->table() !!}
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/clients/index.js'
    ])
    {{-- <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script> --}}
    {!! $dataTable->scripts() !!}
    {{-- <script>
        var table = $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: '/admin/clients/data/1',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
            ],
            initComplete: function () {
                this.api().columns().every(function () {
                    var column = this;
                    $('input', column.header().nextElementSibling).on('keyup change', function () {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });
    </script> --}}
@endpush
