<!-- Order Metrics Widget for Admin Dashboard -->
<div class="col-lg-12 mb-4">
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Thống kê đơn hàng (VIDEC 2026)</h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                Xem tất cả <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2">
                    <div class="p-3 border-end">
                        <h4 class="mb-0 text-primary">{{ $orderStats['total_orders'] ?? 0 }}</h4>
                        <small class="text-muted">Tổng đơn hàng</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border-end">
                        <h4 class="mb-0 text-success">{{ $orderStats['paid_orders'] ?? 0 }}</h4>
                        <small class="text-muted">Đã thanh toán</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border-end">
                        <h4 class="mb-0 text-warning">{{ $orderStats['unpaid_orders'] ?? 0 }}</h4>
                        <small class="text-muted">Chưa thanh toán</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border-end">
                        <h4 class="mb-0 text-info">{{ number_format($orderStats['payment_success_rate'] ?? 0, 1) }}%</h4>
                        <small class="text-muted">Tỷ lệ thành công</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 border-end">
                        <h4 class="mb-0 text-dark">{{ number_format($orderStats['total_revenue'] ?? 0, 0) }}</h4>
                        <small class="text-muted">Doanh thu (VND)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3">
                        <h4 class="mb-0 text-secondary">{{ number_format($orderStats['avg_order_value'] ?? 0, 0) }}</h4>
                        <small class="text-muted">Giá trung bình</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
