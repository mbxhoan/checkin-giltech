@extends('admin.layouts.templates.page-detail', [
    'pageTitle' => "Quản lý promo codes {$event->code}",
    'colLeft'   => 'col-lg-12',
    'colRight'  => 'd-none',
])

@section('buttons')
    <div class="buttons">
        <a href="{{ url("/admin/events/{$event->id}/edit") }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
        <a href="{{ url("/admin/events/{$event->id}/tickets") }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="ticket" prefix="fa-solid"/>
            Vé
        </a>
        <a href="{{ url("/admin/events/{$event->id}/promo-codes") }}" class="btn btn-outline-secondary btn-sm align-self-center mb-lg-0 mb-2">
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
                        <h5 class="mb-1">Danh sách promo code</h5>
                        <div class="text-muted small">
                            Dùng cho API <code>/api/orders/{orderId}/apply-promo</code>.
                        </div>
                    </div>
                    <span class="badge bg-primary align-self-center">
                        {{ $promoCodes->count() }} mã
                    </span>
                </div>

                @include('admin.events.promo-codes._list', [
                    'event' => $event,
                    'promoCodes' => $promoCodes,
                    'selectedPromoCode' => $selectedPromoCode,
                ])
            </div>
        </div>

        <div class="col-lg-5">
            <div class="p-3 bg-light rounded shadow-sm h-100 position-sticky" style="top: 1rem;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">
                            {{ $selectedPromoCode ? 'Cập nhật promo code' : 'Tạo promo code mới' }}
                        </h5>
                        @if ($selectedPromoCode)
                            <div class="text-muted small">
                                Đang sửa: <code>{{ $selectedPromoCode->code }}</code>
                            </div>
                        @endif
                    </div>
                    @if ($selectedPromoCode)
                        <a href="{{ url("/admin/events/{$event->id}/promo-codes") }}" class="btn btn-outline-secondary btn-sm">
                            Huỷ
                        </a>
                    @endif
                </div>

                @include('admin.events.promo-codes._form', [
                    'event' => $event,
                    'selectedPromoCode' => $selectedPromoCode,
                    'formValues' => $formValues,
                ])
            </div>
        </div>
    </div>
@endsection
