@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Quản lý Đơn hàng"
])

@section('title')
    Danh sách Đơn hàng
    <span class="badge bg-primary">{{ $orders->total() }}</span>
@endsection

@section('buttons')
    <a href="{{ route('admin.orders.export') }}" class="btn btn-sm btn-outline-secondary ms-2">
        <i class="fa-solid fa-download"></i> Xuất CSV
    </a>
@endsection

@section('primary-content')
<div class="card">
    <div class="card-header bg-light">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <form method="GET" class="d-flex gap-2" id="filterForm">
                    <input type="email" name="email" class="form-control form-control-sm" 
                        placeholder="Email khách hàng" value="{{ request('email') }}">
                </form>
            </div>
            <div class="col-md-2">
                <select name="event_id" class="form-select form-select-sm" onchange="filterOrders()">
                    <option value="">Tất cả sự kiện</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="filterOrders()">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select form-select-sm" onchange="filterOrders()">
                    <option value="">Tất cả thanh toán</option>
                    @foreach($paymentStatuses as $key => $label)
                        <option value="{{ $key }}" {{ request('payment_status') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" form="filterForm" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-filter"></i> Lọc
                </button>
            </div>
            @if(request()->hasAny(['email', 'event_id', 'status', 'payment_status']))
                <div class="col-md-1">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa-solid fa-xmark"></i> Xóa
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Row -->
    <div class="card-body bg-light border-bottom">
        <div class="row text-center">
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-primary">{{ $stats['total_orders'] }}</h5>
                    <small class="text-muted">Tổng đơn hàng</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-success">{{ $stats['paid_orders'] }}</h5>
                    <small class="text-muted">Đã thanh toán</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-warning">{{ $stats['unpaid_orders'] }}</h5>
                    <small class="text-muted">Chưa thanh toán</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-info">{{ number_format($stats['payment_success_rate'], 1) }}%</h5>
                    <small class="text-muted">Tỷ lệ thành công</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-dark">{{ number_format($stats['total_revenue'], 0) }}</h5>
                    <small class="text-muted">Doanh thu (VND)</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2">
                    <h5 class="mb-0 text-secondary">{{ number_format($stats['avg_order_value'], 0) }}</h5>
                    <small class="text-muted">Trung bình</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 15%">Mã đơn hàng</th>
                    <th style="width: 20%">Khách hàng</th>
                    <th style="width: 15%">Sự kiện</th>
                    <th style="width: 12%">Trạng thái</th>
                    <th style="width: 12%">Số tiền</th>
                    <th style="width: 15%">Ngày tạo</th>
                    <th style="width: 11%" class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold">
                                {{ $order->code }}
                            </a>
                        </td>
                        <td>
                            <small>
                                <strong>{{ $order->portalUser?->name ?? 'N/A' }}</strong><br>
                                <span class="text-muted">{{ $order->portalUser?->email }}</span>
                            </small>
                        </td>
                        <td>
                            {{ $order->event?->name ?? 'N/A' }}
                        </td>
                        <td>
                            @switch($order->status)
                                @case('NEW')
                                    <span class="badge bg-secondary">Mới</span>
                                    @break
                                @case('PENDING')
                                    <span class="badge bg-warning">Đang chờ</span>
                                    @break
                                @case('pending_payment')
                                    <span class="badge bg-info text-dark">Đang thanh toán online</span>
                                    @break
                                @case('pending_cash')
                                    <span class="badge bg-warning text-dark">Chờ thu tiền mặt</span>
                                    @break
                                @case('PAID')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                    @break
                                @case('EXPIRED')
                                    <span class="badge bg-danger">Hết hạn</span>
                                    @break
                                @case('CANCELLED')
                                    <span class="badge bg-dark">Đã hủy</span>
                                    @break
                                @case('REFUNDED')
                                    <span class="badge bg-info">Đã hoàn</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">{{ $order->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <strong>{{ number_format($order->total_amount, 0) }}</strong>
                            @if($order->promo_code_id)
                                <br>
                                <small class="text-success">
                                    -{{ $order->promoCode?->discount_percent }}% ({{ $order->promoCode?->code }})
                                </small>
                            @endif
                        </td>
                        <td>
                            <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary" 
                                    title="Chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if(in_array($order->status, ['NEW', 'PENDING']))
                                    <button type="button" class="btn btn-outline-warning" 
                                        onclick="confirmCancel('{{ $order->id }}');"
                                        title="Hủy đơn">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <p class="text-muted mb-0">Không có đơn hàng nào</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="cancelForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do hủy (không bắt buộc)</label>
                        <textarea name="reason" class="form-control" rows="3" 
                            placeholder="Nhập lý do hủy..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">Hủy đơn hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('admin_js')
<script>
function filterOrders() {
    document.getElementById('filterForm').submit();
}

function confirmCancel(orderId) {
    const form = document.getElementById('cancelForm');
    form.action = '/admin/orders/' + orderId + '/cancel';
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}
</script>
@endpush
