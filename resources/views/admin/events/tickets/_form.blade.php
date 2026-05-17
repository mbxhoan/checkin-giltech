@php
    $metadataJson = $formValues['metadata_json'] ?? null;
    $groupAllowNone = (bool) ($formValues['group_allow_none'] ?? true);
@endphp

<form
    action="{{ $selectedTicket
        ? url("/admin/events/{$event->id}/tickets/{$selectedTicket->id}")
        : url("/admin/events/{$event->id}/tickets")
    }}"
    method="POST"
>
    @csrf
    <input type="hidden" name="event_code" value="{{ $event->code }}">
    @if ($selectedTicket)
        @method('PUT')
    @endif

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'code',
            'fieldName' => 'code',
            'value' => $formValues['code'] ?? null,
            'type' => 'text',
            'label' => 'Mã vé',
            'placeholder' => 'VC26-CF-VOSA',
            'required' => true,
            'formClass' => 'mb-3 col-md-7',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'sort_order',
            'fieldName' => 'sort_order',
            'value' => $formValues['sort_order'] ?? 10,
            'type' => 'number',
            'label' => 'Thứ tự',
            'placeholder' => 10,
            'required' => true,
            'formClass' => 'mb-3 col-md-5',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'name',
            'fieldName' => 'name',
            'value' => $formValues['name'] ?? null,
            'type' => 'text',
            'label' => 'Tên vé',
            'placeholder' => 'Hội viên VOSA / VOSA Member',
            'required' => true,
            'formClass' => 'mb-3 col-md-12',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'type',
            'fieldName' => 'type',
            'value' => $formValues['type'] ?? null,
            'type' => 'text',
            'label' => 'Loại vé',
            'placeholder' => 'conference',
            'formClass' => 'mb-3 col-md-6',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'price',
            'fieldName' => 'price',
            'value' => $formValues['price'] ?? null,
            'type' => 'number',
            'label' => 'Giá VND',
            'placeholder' => 1500000,
            'required' => true,
            'formClass' => 'mb-3 col-md-6',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'dates_string',
            'fieldName' => 'dates_string',
            'value' => $formValues['dates_string'] ?? null,
            'type' => 'text',
            'label' => 'Ngày checkin',
            'placeholder' => '13/11/2026 - 15/11/2026',
            'formClass' => 'mb-3 col-md-12',
        ])
    </div>

    <div class="row">
        @include('components.form-groups.input-group', [
            'id' => 'dates_valid_from',
            'fieldName' => 'dates_valid_from',
            'value' => $formValues['dates_valid_from'] ?? null,
            'type' => 'date',
            'label' => 'Ngày bắt đầu',
            'formClass' => 'mb-3 col-md-6',
        ])
        @include('components.form-groups.input-group', [
            'id' => 'dates_valid_to',
            'fieldName' => 'dates_valid_to',
            'value' => $formValues['dates_valid_to'] ?? null,
            'type' => 'date',
            'label' => 'Ngày kết thúc',
            'formClass' => 'mb-3 col-md-6',
        ])
    </div>

    <div class="border rounded p-3 mb-3 bg-white">
        <div class="fw-semibold mb-2">Catalog hiển thị</div>
        <div class="row">
            @include('components.form-groups.input-group', [
                'id' => 'group_code',
                'fieldName' => 'group_code',
                'value' => $formValues['group_code'] ?? null,
                'type' => 'text',
                'label' => 'Mã nhóm',
                'placeholder' => 'conference',
                'formClass' => 'mb-3 col-md-6',
            ])
            @include('components.form-groups.input-group', [
                'id' => 'max_quantity',
                'fieldName' => 'max_quantity',
                'value' => $formValues['max_quantity'] ?? 1,
                'type' => 'number',
                'label' => 'SL tối đa',
                'placeholder' => 1,
                'formClass' => 'mb-3 col-md-6',
            ])
        </div>

        <div class="row">
            @include('components.form-groups.input-group', [
                'id' => 'group_label_vi',
                'fieldName' => 'group_label_vi',
                'value' => $formValues['group_label_vi'] ?? null,
                'type' => 'text',
                'label' => 'Tên nhóm tiếng Việt',
                'placeholder' => 'Hội nghị',
                'formClass' => 'mb-3 col-md-6',
            ])
            @include('components.form-groups.input-group', [
                'id' => 'group_label_en',
                'fieldName' => 'group_label_en',
                'value' => $formValues['group_label_en'] ?? null,
                'type' => 'text',
                'label' => 'Tên nhóm tiếng Anh',
                'placeholder' => 'Conference Ticket',
                'formClass' => 'mb-3 col-md-6',
            ])
        </div>

        <div class="row">
            @include('components.form-groups.input-group', [
                'id' => 'display_name_vi',
                'fieldName' => 'display_name_vi',
                'value' => $formValues['display_name_vi'] ?? null,
                'type' => 'text',
                'label' => 'Tên hiển thị VI',
                'placeholder' => 'Nha sĩ Việt Nam',
                'formClass' => 'mb-3 col-md-6',
            ])
            @include('components.form-groups.input-group', [
                'id' => 'display_name_en',
                'fieldName' => 'display_name_en',
                'value' => $formValues['display_name_en'] ?? null,
                'type' => 'text',
                'label' => 'Tên hiển thị EN',
                'placeholder' => 'Vietnamese Dentist',
                'formClass' => 'mb-3 col-md-6',
            ])
        </div>

        <div class="row">
            @include('components.form-groups.input-group', [
                'id' => 'display_price_usd',
                'fieldName' => 'display_price_usd',
                'value' => $formValues['display_price_usd'] ?? null,
                'type' => 'number',
                'label' => 'Giá USD',
                'placeholder' => 80,
                'formClass' => 'mb-3 col-md-6',
            ])
            <div class="mb-3 col-md-6">
                <input type="hidden" name="group_allow_none" value="0">
                @include('components.form-groups.input-group', [
                    'id' => 'group_allow_none',
                    'fieldName' => 'group_allow_none',
                    'value' => 1,
                    'type' => 'switch',
                    'label' => 'Cho phép "Không tham gia"',
                    'showLabelTop' => true,
                    'checked' => $groupAllowNone,
                    'formClass' => 'mb-0',
                ])
            </div>
        </div>

        <div class="row">
            @include('components.form-groups.input-group', [
                'id' => 'group_none_label_vi',
                'fieldName' => 'group_none_label_vi',
                'value' => $formValues['group_none_label_vi'] ?? null,
                'type' => 'text',
                'label' => 'Nhãn không tham gia VI',
                'placeholder' => 'Không tham gia',
                'formClass' => 'mb-3 col-md-6',
            ])
            @include('components.form-groups.input-group', [
                'id' => 'group_none_label_en',
                'fieldName' => 'group_none_label_en',
                'value' => $formValues['group_none_label_en'] ?? null,
                'type' => 'text',
                'label' => 'Nhãn không tham gia EN',
                'placeholder' => 'None',
                'formClass' => 'mb-3 col-md-6',
            ])
        </div>
    </div>

    <div class="border rounded p-3 mb-3 bg-white">
        <div class="fw-semibold mb-2">Metadata JSON</div>
        <div class="text-muted small mb-2">
            Nếu nhập JSON, dữ liệu đó sẽ được dùng làm nền rồi merge với các field ở trên.
        </div>
        @include('components.form-groups.input-group', [
            'id' => 'metadata_json',
            'fieldName' => 'metadata_json',
            'value' => $metadataJson,
            'type' => 'textarea',
            'label' => 'metadata_json',
            'rows' => 14,
            'placeholder' => '{ "group": {...}, "display": {...}, "rules": {...} }',
            'formClass' => 'mb-0',
        ])
    </div>

    <div class="d-flex justify-content-between align-items-center gap-2">
        <div class="text-muted small">
            Dữ liệu lưu vào bảng `tickets` và được API catalog đọc lại ngay.
        </div>
        <button type="submit" class="btn btn-primary">
            <x-icon name="save" />
            {{ $selectedTicket ? 'Cập nhật vé' : 'Tạo vé' }}
        </button>
    </div>
</form>
