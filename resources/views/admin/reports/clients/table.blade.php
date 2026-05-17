@php
    $customFieldTemplates = $event->getCustomFieldTemplates();
    $showTicketSummary = $showTicketSummary ?? false;
    $countCol = 10 + ($showTicketSummary ? 1 : 0);
@endphp

<span class="text-xs text-secondary">
    <x-icon name="arrow-left" />
    Giữ Shift và lăn chuột để kéo ngang
    <x-icon name="arrow-right" />
</span>

<table class="table table-sm w-100">
    <caption>Tổng hợp dữ liệu</caption>
    <thead>
        <tr>
            <th>ID</th>
            <th>
                <input type="text" placeholder="QRCODE" class="filter-input" name="qrcode" value="{{ $qrcode ?? null }}" class="" autofocus>
            </th>
            <th scope="col" class="col-2">
                <input type="text" placeholder="HỌ, TÊN" class="filter-input" name="name" class="{{ $name ?? null }}">
            </th>
            <th scope="col" class="col-2">
                <input type="text" placeholder="EMAIL" class="filter-input" name="email" class="{{ $email ?? null }}">
            </th>
            <th>LOẠI</th>
            <th>TRẠNG THÁI</th>
            <th>NGUỒN</th>
            <th>Bởi</th>
            <th>Sửa</th>
            <th>Tạo</th>
            @if ($showTicketSummary)
                <th>Vé</th>
            @endif
            @foreach ($customFieldTemplates as $templateName => $templateAttr)
                @php
                    $countCol++;
                @endphp

                <th>
                    {{ $templateAttr['desc'] ?? strtoupper($templateName) }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody id="clients-table-body">
        @include('admin.reports.clients._tbody', [
            'event'                 => $event,
            'clients'               => $clients,
            'customFieldTemplates'  => $customFieldTemplates,
        ])
    </tbody>
</table>

<div class="d-flex justify-content-center" id="pagination-links">
    {!! $clients->links() !!}
</div>

@push('admin_css')
    <style>
        table {
            overflow: auto;
            height: auto;
        }
        table thead tr th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
@endpush
