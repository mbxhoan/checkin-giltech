@extends('admin.layouts.templates.page-detail', [
    'pageTitle' => "Quản lý vé {$event->code}",
    'colLeft'   => 'col-lg-12',
    'colRight'  => 'd-none',
])

@section('buttons')
    <div class="buttons">
        <a href="{{ url("/admin/events/{$event->id}/edit") }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
        <a href="{{ url("/admin/clients/index/{$event->id}") }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="users"/>
            Khách mời
        </a>
        <a href="{{ route('admin.events.promo-codes.index', $event) }}" class="btn btn-warning btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="percent" prefix="fa-solid"/>
            Khuyến mãi
        </a>
        <a href="{{ url("/admin/events/{$event->id}/tickets") }}" class="btn btn-outline-secondary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="rotate"/>
            Tải lại
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="p-3 bg-light rounded shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">Danh sách vé</h5>
                        <div class="text-muted small">
                            Nguồn dữ liệu cho API <code>/api/events/{{ $event->id }}/tickets</code>.
                        </div>
                    </div>
                    <span class="badge bg-primary align-self-center">
                        {{ $tickets->count() }} vé
                    </span>
                </div>

                @include('admin.events.tickets._list', [
                    'event' => $event,
                    'tickets' => $tickets,
                    'selectedTicket' => $selectedTicket,
                ])
            </div>
        </div>

        <div class="col-lg-5">
            <div class="p-3 bg-light rounded shadow-sm h-100 position-sticky" style="top: 1rem;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">
                            {{ $selectedTicket ? 'Cập nhật vé' : 'Tạo vé mới' }}
                        </h5>
                        @if ($selectedTicket)
                            <div class="text-muted small">
                                Đang sửa: <code>{{ $selectedTicket->code }}</code>
                            </div>
                        @endif
                    </div>
                    @if ($selectedTicket)
                        <a href="{{ url("/admin/events/{$event->id}/tickets") }}" class="btn btn-outline-secondary btn-sm">
                            Huỷ
                        </a>
                    @endif
                </div>

                @include('admin.events.tickets._form', [
                    'event' => $event,
                    'selectedTicket' => $selectedTicket,
                    'formValues' => $formValues,
                ])
            </div>
        </div>
    </div>
@endsection
