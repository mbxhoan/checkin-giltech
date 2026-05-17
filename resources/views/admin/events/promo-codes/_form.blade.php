@php
    $discountType = $formValues['discount_type'] ?? 'percentage';
@endphp

<form
    action="{{ $selectedPromoCode
        ? url("/admin/events/{$event->id}/promo-codes/{$selectedPromoCode->id}")
        : url("/admin/events/{$event->id}/promo-codes")
    }}"
    method="POST"
>
    @csrf
    <input type="hidden" name="event_id" value="{{ $event->id }}">
    <input type="hidden" name="discount_type" value="percentage">
    @if ($selectedPromoCode)
        @method('PUT')
    @endif

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'code',
            'fieldName' => 'code',
            'value' => $formValues['code'] ?? null,
            'type' => 'text',
            'label' => 'Mã promo',
            'placeholder' => 'VIDEC10',
            'required' => true,
            'formClass' => 'mb-3 col-md-7',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'discount_value',
            'fieldName' => 'discount_value',
            'value' => $formValues['discount_value'] ?? 10,
            'type' => 'number',
            'label' => 'Giảm (%)',
            'placeholder' => 10,
            'required' => true,
            'formClass' => 'mb-3 col-md-5',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'max_discount_amount',
            'fieldName' => 'max_discount_amount',
            'value' => $formValues['max_discount_amount'] ?? null,
            'type' => 'number',
            'label' => 'Giảm tối đa (VND)',
            'placeholder' => 100000,
            'formClass' => 'mb-3 col-md-6',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'min_order_amount',
            'fieldName' => 'min_order_amount',
            'value' => $formValues['min_order_amount'] ?? null,
            'type' => 'number',
            'label' => 'Đơn tối thiểu (VND)',
            'placeholder' => 500000,
            'formClass' => 'mb-3 col-md-6',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'usage_limit',
            'fieldName' => 'usage_limit',
            'value' => $formValues['usage_limit'] ?? null,
            'type' => 'number',
            'label' => 'Số lượt dùng',
            'placeholder' => 100,
            'formClass' => 'mb-3 col-md-6',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'status',
            'fieldName' => 'status',
            'value' => $formValues['status'] ?? 'ACTIVE',
            'type' => 'select',
            'label' => 'Trạng thái',
            'options' => [
                'ACTIVE' => 'ACTIVE',
                'INACTIVE' => 'INACTIVE',
            ],
            'formClass' => 'mb-3 col-md-6',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'starts_at',
            'fieldName' => 'starts_at',
            'value' => $formValues['starts_at'] ?? null,
            'type' => 'datetime-local',
            'label' => 'Bắt đầu',
            'formClass' => 'mb-3 col-md-6',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'ends_at',
            'fieldName' => 'ends_at',
            'value' => $formValues['ends_at'] ?? null,
            'type' => 'datetime-local',
            'label' => 'Kết thúc',
            'formClass' => 'mb-3 col-md-6',
        ])
    </div>

    <div class="border rounded p-3 mb-3 bg-white">
        <div class="fw-semibold mb-2">Metadata JSON</div>
        <div class="text-muted small mb-2">
            Không bắt buộc. Dùng để lưu thông tin phụ nếu sau này cần mở rộng promo code.
        </div>
        @include('components.form-groups.input-group', [
            'id' => 'metadata_json',
            'fieldName' => 'metadata_json',
            'value' => $formValues['metadata_json'] ?? null,
            'type' => 'textarea',
            'label' => 'metadata_json',
            'rows' => 10,
            'placeholder' => '{ "note": "..." }',
            'formClass' => 'mb-0',
        ])
    </div>

    <div class="d-flex justify-content-between align-items-center gap-2">
        <div class="text-muted small">
            Promo code này gắn với sự kiện <code>{{ $event->code }}</code>.
        </div>
        <button type="submit" class="btn btn-primary">
            <x-icon name="save" />
            {{ $selectedPromoCode ? 'Cập nhật promo code' : 'Tạo promo code' }}
        </button>
    </div>
</form>
