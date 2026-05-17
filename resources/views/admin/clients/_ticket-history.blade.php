@php
    $ticketHistory = $ticketHistory ?? [];
    $summary = $ticketHistory['summary'] ?? [];
    $ticketLines = collect($ticketHistory['ticket_lines'] ?? []);
    $orders = collect($ticketHistory['orders'] ?? []);
    $latestOrder = $ticketHistory['latest_order'] ?? null;
@endphp

@if ($orders->isNotEmpty() || $ticketLines->isNotEmpty())
    <div class="bg-white border rounded shadow-sm p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h5 class="mb-1">Vé đã và đang mua</h5>
                <div class="text-muted small">
                    Tổng hợp từ các đơn hàng của khách trong cùng sự kiện.
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary">
                    {{ $summary['total_orders'] ?? $orders->count() }} đơn
                </span>
                @if ($latestOrder)
                    <div class="text-muted small mt-1">
                        Đơn gần nhất: <code>{{ $latestOrder['no'] }}</code>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 bg-light h-100">
                    <div class="text-muted small">Đã thanh toán</div>
                    <div class="fw-bold text-success">
                        {{ $summary['formatted_paid_revenue'] ?? '0 VND' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 bg-light h-100">
                    <div class="text-muted small">Doanh thu thực tế</div>
                    <div class="fw-bold text-primary">
                        {{ $summary['formatted_gross_revenue'] ?? '0 VND' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 bg-light h-100">
                    <div class="text-muted small">Đã thanh toán</div>
                    <div class="fw-bold">
                        {{ $summary['paid_orders'] ?? 0 }} đơn
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 bg-light h-100">
                    <div class="text-muted small">Chưa thanh toán</div>
                    <div class="fw-bold">
                        {{ $summary['unpaid_orders'] ?? 0 }} đơn
                    </div>
                </div>
            </div>
        </div>

        @if ($ticketLines->isNotEmpty())
            <div class="mb-3">
                <div class="text-muted small mb-2">Danh sách vé</div>
                <div class="row g-2">
                    @foreach ($ticketLines as $line)
                        <div class="col-xl-4 col-md-6">
                            <div class="border rounded p-2 h-100 bg-light">
                                <div class="fw-semibold text-truncate">
                                    {{ $line['display_name'] }}
                                </div>
                                <div class="small text-muted">
                                    {{ $line['quantity'] }} vé
                                    @if (!empty($line['formatted_revenue']))
                                        <span class="mx-1">•</span>
                                        {{ $line['formatted_revenue'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($orders->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Đơn</th>
                            <th class="text-nowrap">Trạng thái</th>
                            <th class="text-nowrap">Cập nhật</th>
                            <th class="text-nowrap text-end col-1">Tổng tiền</th>
                            <th>Vé trong đơn</th>
                            <th class="text-nowrap text-center col-2">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $order['no'] }}
                                </td>
                                <td>
                                    <span class="badge {{ $order['status_class'] }}">
                                        {{ $order['status_label'] }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    @humanize_date($order['updated_at'], 'd/m/Y H:i')
                                </td>
                                <td class="text-end">
                                    {{ $order['formatted_total'] }}
                                </td>
                                <td>
                                    {{ $order['item_text'] ?: 'Chưa có vé' }}
                                </td>
                                <td class="text-center">
                                    @if (in_array($order['status'] ?? null, ['unpaid', 'pending_payment', 'EXPIRED', 'expired'], true))
                                        <button type="button" class="btn btn-success btn-xs"
                                            data-bs-toggle="modal"
                                            data-bs-target="#quickCashConfirmModal-{{ $order['id'] }}">
                                            Xác nhận tiền mặt
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif

@foreach ($orders as $order)
    @if (in_array($order['status'] ?? null, ['unpaid', 'pending_payment', 'EXPIRED', 'expired'], true))
        <div class="modal fade" id="quickCashConfirmModal-{{ $order['id'] }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận đã nhận thanh toán tiền mặt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.orders.mark-paid', $order['id']) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <small>
                                    Xác nhận cho đơn <strong>{{ $order['no'] }}</strong>.
                                    Hành động này chỉ áp dụng cho đúng đơn đang chọn.
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số tiền đã nhận (VND) *</label>
                                <input type="number" name="amount_received" class="form-control"
                                    value="{{ $order['total_amount'] }}"
                                    min="0"
                                    step="1"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mã biên nhận (không bắt buộc)</label>
                                <input type="text" name="receipt_code" class="form-control"
                                    maxlength="100"
                                    placeholder="CASH-000001">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú (không bắt buộc)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                    placeholder="Ghi chú về việc thu tiền"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-success">Xác nhận ngay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
