@php
    $summary = $analytics['summary'] ?? [];
    $tickets = collect($analytics['tickets'] ?? []);
    $event = $analytics['event'] ?? [];
@endphp

<div class="bg-white border rounded shadow-sm p-3 mb-3" id="videc-ticket-analytics" data-analytics='@json($analytics)'>
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">
                Phân tích vé {{ $event['name'] ?? 'VIDEC 2026' }}
            </h5>
            <div class="text-muted small">
                Doanh thu, trạng thái đơn hàng và danh sách vé đang được mua nhiều nhất.
            </div>
        </div>
        <span class="badge bg-primary align-self-center">
            {{ $event['code'] ?? 'videc-2026' }}
        </span>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-muted small">Doanh thu đã nhận</div>
                <div class="fw-bold fs-5 text-success">
                    {{ $summary['formatted_paid_revenue'] ?? '0 VND' }}
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-muted small">Doanh thu thực tế</div>
                <div class="fw-bold fs-5 text-primary">
                    {{ $summary['formatted_gross_revenue'] ?? '0 VND' }}
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-muted small">Tổng đơn hàng</div>
                <div class="fw-bold fs-5">
                    {{ $summary['total_orders'] ?? 0 }}
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-muted small">Đã thanh toán</div>
                <div class="fw-bold fs-5 text-success">
                    {{ $summary['paid_orders'] ?? 0 }}
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-muted small">Chưa thanh toán</div>
                <div class="fw-bold fs-5 text-warning">
                    {{ $summary['unpaid_orders'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>

    @if (!empty($summary['top_ticket']))
        <div class="alert alert-light border d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="text-muted small">Vé đang được mua nhiều nhất</div>
                <div class="fw-semibold">
                    {{ $summary['top_ticket']['display_name'] ?? 'N/A' }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-semibold">
                    {{ $summary['top_ticket']['quantity'] ?? 0 }} vé
                </div>
                <div class="text-muted small">
                    {{ $summary['formatted_top_ticket_revenue'] ?? '0 VND' }}
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="border rounded p-3 bg-light h-100">
                <h6 class="mb-2">Doanh thu</h6>
                <div style="height: 260px;">
                    <canvas id="videc-revenue-status-chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="border rounded p-3 bg-light h-100">
                <h6 class="mb-2">Trạng thái đơn hàng</h6>
                <div style="height: 260px;">
                    <canvas id="videc-order-status-chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="border rounded p-3 bg-light h-100">
                <h6 class="mb-2">Số lượng vé theo loại</h6>
                <div style="height: 320px;">
                    <canvas id="videc-ticket-quantity-chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="border rounded p-3 bg-light h-100">
                <h6 class="mb-2">Doanh thu các vé</h6>
                <div style="height: 320px;">
                    <canvas id="videc-ticket-revenue-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <h6 class="mb-2">Tổng hợp theo vé</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Vé</th>
                        <th class="text-end">Số lượng</th>
                        <th class="text-end">Đã thu</th>
                        <th class="text-end">Chưa thu</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $ticket['display_name'] }}
                                </div>
                                <div class="text-muted small">
                                    {{ $ticket['code'] ?? $ticket['ticket_code'] ?? '' }}
                                </div>
                            </td>
                            <td class="text-end">
                                {{ $ticket['quantity'] ?? 0 }}
                            </td>
                            <td class="text-end">
                                {{ $ticket['formatted_paid_revenue'] ?? '0 VND' }}
                            </td>
                            <td class="text-end">
                                {{ $ticket['formatted_unpaid_revenue'] ?? '0 VND' }}
                            </td>
                            <td class="text-end fw-semibold">
                                {{ $ticket['formatted_revenue'] ?? '0 VND' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Chưa có dữ liệu vé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
