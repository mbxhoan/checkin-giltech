@extends('admin.layouts.templates.page-index', [
    'pageTitle' => 'Quản lý vé'
])

@section('title')
    Quản lý vé:
@endsection

@section('buttons')
    <div class="buttons">
        <a href="" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#selectEventModal"
        >
            <x-icon name="ticket" prefix="fa-solid"/>
            Chọn sự kiện
        </a>
        <div class="modal fade" id="selectEventModal" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="selectEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="selectEventModalLabel">
                            Chọn sự kiện để quản lý vé
                            <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                                <x-icon name="plus-square" prefix="fa-regular"/>
                            </a>
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.tickets.select-event-to-manage') }}" method="GET">
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
                                Mở quản lý vé
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('primary-content')
    <div class="p-3 bg-light rounded shadow-sm">
        <div class="mb-3">
            <h5 class="mb-1">Chọn sự kiện</h5>
        </div>

        <div class="border rounded p-3 bg-white">
            <div class="fw-semibold mb-2">Lưu ý</div>
            <ul class="mb-0 small text-muted">
                <li>Quản lý vé vẫn chạy theo từng sự kiện.</li>
                <li>Nếu đang ở trang chi tiết sự kiện, bạn vẫn có thể mở trực tiếp bằng nút "Quản lý vé".</li>
            </ul>
        </div>
    </div>
@endsection
