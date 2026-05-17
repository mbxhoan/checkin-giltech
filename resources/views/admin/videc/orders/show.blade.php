@extends('admin.layouts.templates.page-detail', [
    'pageTitle' => "Chi tiết Đơn hàng"
])

@section('title')
    Đơn hàng: {{ $order->code }}
@endsection

@section('buttons')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
@endsection

@section('primary-content')
<div class="row">
    <!-- Order Header -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
                @switch($order->status)
                    @case('NEW')
                        <span class="badge bg-secondary fs-6">Mới</span>
                        @break
                    @case('PENDING')
                        <span class="badge bg-warning fs-6">Đang chờ</span>
                        @break
                    @case('unpaid')
                        <span class="badge bg-warning fs-6">Chưa thanh toán</span>
                        @break
                    @case('pending_payment')
                        <span class="badge bg-info fs-6">Đang thanh toán online</span>
                        @break
                    @case('pending_cash')
                        <span class="badge bg-warning fs-6">Chờ thu tiền mặt</span>
                        @break
                    @case('PAID')
                        <span class="badge bg-success fs-6">Đã thanh toán</span>
                        @break
                    @case('paid')
                        <span class="badge bg-success fs-6">Đã thanh toán</span>
                        @break
                    @case('EXPIRED')
                        <span class="badge bg-danger fs-6">Hết hạn</span>
                        @break
                    @case('CANCELLED')
                        <span class="badge bg-dark fs-6">Đã hủy</span>
                        @break
                    @case('REFUNDED')
                        <span class="badge bg-info fs-6">Đã hoàn</span>
                        @break
                @endswitch
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><small class="text-muted">Mã đơn hàng</small></p>
                        <p class="fw-bold mb-3">{{ $order->code }}</p>

                        <p class="mb-1"><small class="text-muted">Số đơn</small></p>
                        <p class="mb-3">{{ $order->no }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><small class="text-muted">Ngày tạo</small></p>
                        <p class="mb-3">{{ $order->created_at->format('d/m/Y H:i:s') }}</p>

                        <p class="mb-1"><small class="text-muted">Hết hạn lúc</small></p>
                        <p class="mb-3">{{ $order->expiry_date ? $order->expiry_date->format('d/m/Y H:i:s') : 'N/A' }}</p>
                    </div>
                </div>

                @if($order->paid_at)
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Thanh toán lúc</small></p>
                            <p class="mb-3 text-success fw-bold">{{ $order->paid_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                @endif

                @if($order->cancelled_at)
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-warning"></i> Đơn hàng đã bị hủy lúc {{ $order->cancelled_at->format('d/m/Y H:i:s') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><small class="text-muted">Email</small></p>
                        <p class="mb-3"><a href="mailto:{{ $order->portalUser?->email }}">{{ $order->portalUser?->email }}</a></p>

                        <p class="mb-1"><small class="text-muted">Tên</small></p>
                        <p class="mb-3">{{ $order->portalUser?->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><small class="text-muted">Điện thoại</small></p>
                        <p class="mb-3">{{ $order->portalUser?->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Info -->
        @if($order->event)
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin sự kiện</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Sự kiện</small></p>
                            <p class="mb-3 fw-bold">{{ $order->event->name }}</p>

                            <p class="mb-1"><small class="text-muted">Mã sự kiện</small></p>
                            <p class="mb-3">{{ $order->event->code }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Ngày sự kiện</small></p>
                            <p class="mb-3">{{ $order->event->date_start ? $order->event->date_start->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Order Items -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Các vé được đặt</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr class="table-light">
                            <th>Vé</th>
                            <th>Mã vé</th>
                            <th>Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderSnapshot['items'] as $item)
                            <tr>
                                <td>{{ $item['ticket_name'] }}</td>
                                <td><code>{{ $item['ticket_code'] }}</code></td>
                                <td>{{ $item['quantity'] }}</td>
                                <td class="text-end">{{ number_format(($orderSnapshot['pricing']['subtotal'] / count($orderSnapshot['items'])), 0) }} VND</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Không có vé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Chi tiết giá tiền</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2 d-flex justify-content-between">
                            <span>Tổng tiền:</span>
                            <strong>{{ number_format($orderSnapshot['pricing']['subtotal'], 0) }} VND</strong>
                        </p>
                        @if($orderSnapshot['pricing']['discount_amount'] > 0)
                            <p class="mb-2 d-flex justify-content-between text-success">
                                <span>Giảm giá (@if($orderSnapshot['pricing']['promo_code']){{ $orderSnapshot['pricing']['promo_code'] }} - @endif{{ $orderSnapshot['pricing']['discount_percent'] }}%):</span>
                                <strong>-{{ number_format($orderSnapshot['pricing']['discount_amount'], 0) }} VND</strong>
                            </p>
                        @endif
                        @if($orderSnapshot['pricing']['tax_amount'] > 0)
                            <p class="mb-2 d-flex justify-content-between">
                                <span>Thuế:</span>
                                <strong>{{ number_format($orderSnapshot['pricing']['tax_amount'], 0) }} VND</strong>
                            </p>
                        @endif
                        <hr>
                        <p class="d-flex justify-content-between">
                            <span class="fw-bold">Tổng cộng:</span>
                            <span class="fw-bold fs-5 text-primary">{{ number_format($orderSnapshot['pricing']['total_amount'], 0) }} VND</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Attempts -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Lịch sử thanh toán</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr class="table-light">
                            <th>Lần thử #</th>
                            <th>Trạng thái</th>
                            <th>Số tiền</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentAttempts as $attempt)
                            <tr>
                                <td>#{{ $attempt->id }}</td>
                                <td>
                            @if($attempt->status === 'COMPLETED')
                                <span class="badge bg-success">Hoàn tất</span>
                            @elseif($attempt->status === 'success')
                                <span class="badge bg-success">Hoàn tất</span>
                            @elseif($attempt->status === 'pending_cash')
                                <span class="badge bg-warning text-dark">Chờ thu tiền</span>
                            @elseif($attempt->status === 'PENDING')
                                <span class="badge bg-warning">Đang chờ</span>
                            @elseif($attempt->status === 'redirected')
                                <span class="badge bg-info text-dark">Đã chuyển cổng</span>
                            @else
                                <span class="badge bg-danger">{{ $attempt->status }}</span>
                            @endif
                                </td>
                                <td>{{ number_format($attempt->amount, 0) }} VND</td>
                                <td>{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                        onclick="showPaymentDetails({{ $attempt->id }});"
                                        title="Chi tiết thanh toán">
                                        <i class="fa-solid fa-info-circle"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có lần thanh toán nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($orderSnapshot['payment']['cash_payment_logs']))
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Lịch sử thu tiền mặt</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="table-light">
                                <th>Mã biên nhận</th>
                                <th>Số tiền nhận</th>
                                <th>Tiền thừa</th>
                                <th>Nhân viên</th>
                                <th>Thời điểm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderSnapshot['payment']['cash_payment_logs'] as $cashLog)
                                <tr>
                                    <td>{{ $cashLog['receipt_code'] ?? 'N/A' }}</td>
                                    <td>{{ number_format($cashLog['amount_received'], 0) }} VND</td>
                                    <td>{{ number_format($cashLog['change_amount'], 0) }} VND</td>
                                    <td>{{ $cashLog['cashier']['name'] ?? ($cashLog['cashier_user_id'] ?? 'N/A') }}</td>
                                    <td>{{ $cashLog['confirmed_at'] ? \Carbon\Carbon::parse($cashLog['confirmed_at'])->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Post-Purchase -->
        <div class="row">
            @if($orderSnapshot['post_purchase']['invoice'])
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Hóa đơn</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <small class="text-muted">Số hóa đơn:</small><br>
                                <strong>{{ $orderSnapshot['post_purchase']['invoice']['number'] }}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Ngày phát hành:</small><br>
                                {{ $orderSnapshot['post_purchase']['invoice']['issued_at'] ? \Carbon\Carbon::parse($orderSnapshot['post_purchase']['invoice']['issued_at'])->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if(count($orderSnapshot['post_purchase']['ticket_issuances']) > 0)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Vé đã phát hành</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1">
                                <small class="text-muted">Số lượng:</small>
                                <strong>{{ count($orderSnapshot['post_purchase']['ticket_issuances']) }}</strong>
                            </p>
                            <p class="mb-0">
                                <small class="text-muted">Ngày phát hành:</small><br>
                                {{ $orderSnapshot['post_purchase']['ticket_issuances'][0]['issued_at'] ? \Carbon\Carbon::parse($orderSnapshot['post_purchase']['ticket_issuances'][0]['issued_at'])->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Refund Requests -->
        @if(count($orderSnapshot['post_purchase']['refund_requests']) > 0)
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Yêu cầu hoàn tiền</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="table-light">
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Lý do</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderSnapshot['post_purchase']['refund_requests'] as $refund)
                                <tr>
                                    <td>{{ number_format($refund['amount'], 0) }} VND</td>
                                    <td>
                                        @if($refund['status'] === 'PENDING')
                                            <span class="badge bg-warning">Chờ xử lý</span>
                                        @elseif($refund['status'] === 'COMPLETED')
                                            <span class="badge bg-success">Hoàn tất</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $refund['status'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $refund['reason'] ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($refund['created_at'])->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Thao tác</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($canMarkPaid)
                        <button type="button" class="btn btn-success" 
                            onclick="showMarkPaidModal()"
                            title="Xác nhận đã nhận thanh toán tiền mặt">
                            <i class="fa-solid fa-check-circle"></i>
                            Xác nhận đã nhận thanh toán tiền mặt
                        </button>
                    @endif

                    @if($canCancel)
                        <button type="button" class="btn btn-danger" 
                            onclick="showCancelModal()"
                            title="Hủy đơn hàng">
                            <i class="fa-solid fa-ban"></i> Hủy đơn hàng
                        </button>
                    @endif

                    @if($canRefund)
                        <button type="button" class="btn btn-warning" 
                            onclick="showRefundModal()"
                            title="Tạo yêu cầu hoàn tiền">
                            <i class="fa-solid fa-money-bill-wave"></i> Hoàn tiền
                        </button>
                    @endif

                    <button type="button" class="btn btn-outline-primary" 
                        onclick="showResendEmailModal()">
                        <i class="fa-solid fa-envelope"></i> Gửi lại email
                    </button>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Ghi chú</h5>
            </div>
            <div class="card-body">
                @if($order->metadata && isset($order->metadata['manual_payment_marking']))
                    <div class="alert alert-info mb-2">
                        <i class="fa-solid fa-info-circle"></i>
                        <small>
                            <strong>Đánh dấu thủ công:</strong>
                            Lúc {{ $order->metadata['marked_at'] ?? 'N/A' }}<br>
                            {{ $order->metadata['manual_payment_notes'] ?? '' }}
                        </small>
                    </div>
                @endif

                @if($order->payment_method === 'cash_at_event')
                    <div class="alert alert-success mb-2">
                        <i class="fa-solid fa-cash-register"></i>
                        <small>
                            <strong>Thanh toán tiền mặt:</strong>
                            Đơn hàng này đã được xác nhận thanh toán tiền mặt.
                        </small>
                    </div>
                @endif

                @if($order->metadata && isset($order->metadata['cancelled_by_admin']))
                    <div class="alert alert-warning mb-2">
                        <i class="fa-solid fa-warning"></i>
                        <small>
                            <strong>Hủy bởi admin:</strong>
                            {{ $order->metadata['cancellation_reason'] ?? '' }}
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận đã nhận thanh toán tiền mặt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa-solid fa-info-circle"></i>
                        <small>
                            Tác vụ này sẽ ghi nhận đơn hàng là đã thanh toán bằng tiền mặt.
                            Vui lòng nhập đúng số tiền đã nhận trước khi xác nhận.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (không bắt buộc)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                            placeholder="Ghi chú về lý do đánh dấu..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số tiền đã nhận (VND) *</label>
                        <input type="number" name="amount_received" class="form-control"
                            value="{{ $orderSnapshot['pricing']['total_amount'] }}"
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">
                        Xác nhận thanh toán tiền mặt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-warning"></i>
                        <small>Hành động này sẽ hủy đơn hàng. Hãy kiểm tra kỹ trước khi xác nhận.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do hủy (không bắt buộc)</label>
                        <textarea name="reason" class="form-control" rows="3" 
                            placeholder="Nhập lý do hủy..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo yêu cầu hoàn tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.refund', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Số tiền hoàn tiền (VND) *</label>
                        <input type="number" name="amount" class="form-control" 
                            value="{{ $order->total_amount }}"
                            min="1000" 
                            max="{{ $order->total_amount }}"
                            required>
                        <small class="text-muted">Tối đa: {{ number_format($order->total_amount, 0) }} VND</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do hoàn tiền (không bắt buộc)</label>
                        <textarea name="reason" class="form-control" rows="3" 
                            placeholder="Nhập lý do hoàn tiền..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">Tạo yêu cầu hoàn tiền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resend Email Modal -->
<div class="modal fade" id="resendEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gửi lại email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.resend-email', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại email *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="email_type" 
                                id="emailConfirmation" value="confirmation" checked>
                            <label class="form-check-label" for="emailConfirmation">
                                Email xác nhận thanh toán
                            </label>
                        </div>
                        @if($order->invoice)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="email_type" 
                                    id="emailInvoice" value="invoice">
                                <label class="form-check-label" for="emailInvoice">
                                    Email hóa đơn
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Gửi email</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('admin_js')
<script>
function showMarkPaidModal() {
    const modal = new bootstrap.Modal(document.getElementById('markPaidModal'));
    modal.show();
}

function showCancelModal() {
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

function showRefundModal() {
    const modal = new bootstrap.Modal(document.getElementById('refundModal'));
    modal.show();
}

function showResendEmailModal() {
    const modal = new bootstrap.Modal(document.getElementById('resendEmailModal'));
    modal.show();
}
</script>
@endpush
