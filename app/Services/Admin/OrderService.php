<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Order::class);
    }

    /**
     * Get orders with comprehensive filtering and relationships
     */
    public function getOrdersWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->buildFilterQuery($filters);

        return $query->with([
            'event',
            'portalUser',
            'registration',
            'registration.items.ticket',
            'promoCode',
            'paymentAttempts',
            'cashPaymentLogs.cashier',
            'invoice',
            'ticketIssuances',
            'refundRequests',
        ])->latest()->paginate($perPage);
    }

    /**
     * Build filtered query based on filter parameters
     */
    protected function buildFilterQuery(array $filters = []): Builder
    {
        $query = Order::query();

        // Filter by event
        if (!empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            switch ($filters['payment_status']) {
                case 'paid':
                    $query->whereIn('status', ['PAID', 'paid']);
                    break;
                case 'pending_cash':
                    $query->where('status', 'pending_cash');
                    break;
                case 'unpaid':
                    $query->whereIn('status', ['NEW', 'PENDING', 'unpaid', 'pending_payment', 'pending_cash']);
                    break;
                case 'cancelled':
                    $query->whereIn('status', ['CANCELLED', 'cancelled']);
                    break;
                case 'refunded':
                    $query->whereIn('status', ['REFUNDED', 'refunded']);
                    break;
            }
        }

        // Filter by email
        if (!empty($filters['email'])) {
            $query->whereHas('portalUser', function (Builder $q) {
                $q->where('email', 'like', '%' . request('email') . '%');
            });
        }

        // Filter by order code
        if (!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
        }

        // Filter by date range (created)
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filter by promo code
        if (!empty($filters['promo_code_id'])) {
            $query->where('promo_code_id', $filters['promo_code_id']);
        }

        // Filter by amount range
        if (!empty($filters['amount_from'])) {
            $query->where('total_amount', '>=', $filters['amount_from']);
        }
        if (!empty($filters['amount_to'])) {
            $query->where('total_amount', '<=', $filters['amount_to']);
        }

        return $query;
    }

    /**
     * Get complete order snapshot with all related data
     */
    public function getOrderSnapshot(Order $order): array
    {
        $order->load([
            'portalUser',
            'event',
            'registration',
            'registration.items.ticket',
            'promoCode',
            'paymentAttempts',
            'cashPaymentLogs.cashier',
            'invoice',
            'ticketIssuances',
            'refundRequests',
        ]);

        return [
            'id' => $order->id,
            'code' => $order->code,
            'no' => $order->no,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'expires_at' => $order->expiry_date,
            'paid_at' => $order->paid_at,
            'cancelled_at' => $order->cancelled_at,
            'refunded_at' => $order->refunded_at,
            'payment_method' => $order->payment_method,
            'checkin_sync_status' => $order->checkin_sync_status,

            // Customer Info
            'customer' => [
                'portal_user_id' => $order->portal_user_id,
                'email' => $order->portalUser?->email,
                'name' => $order->portalUser?->name,
                'phone' => $order->portalUser?->phone,
            ],

            // Event Info
            'event' => [
                'id' => $order->event_id,
                'name' => $order->event?->name,
                'code' => $order->event?->code,
                'date' => $order->event?->date_start,
                'location' => $order->event?->location,
            ],

            // Registration Info
            'registration' => [
                'id' => $order->registration_id,
                'email' => $order->registration?->email,
                'name' => $order->registration?->name,
                'phone' => $order->registration?->phone,
            ],

            // Order Items (Tickets)
            'items' => $order->registrationItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'ticket_id' => $item->ticket_id,
                    'ticket_name' => $item->ticket?->name,
                    'ticket_code' => $item->ticket?->code,
                    'quantity' => $item->quantity,
                ];
            })->toArray(),

            // Pricing
            'pricing' => [
                'subtotal_amount' => (float) $order->subtotal_amount,
                'discount_amount' => (float) $order->discount_amount,
                'discount_percent' => !empty($order->promo_code_id)
                    ? $order->promoCode?->discount_percent ?? 0
                    : 0,
                'promo_code' => $order->promoCode?->code,
                'promo_code_id' => $order->promo_code_id,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency ?? 'VND',
            ],

            // Payment Info
            'payment' => [
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_attempts' => $order->paymentAttempts->map(function ($attempt) {
                    return [
                        'id' => $attempt->id,
                        'amount' => (float) $attempt->amount,
                        'status' => $attempt->status,
                        'created_at' => $attempt->created_at,
                        'response' => [
                            'merchant_id' => $attempt->merchant_id,
                            'access_code' => $attempt->access_code,
                            'order_info' => $attempt->order_info,
                            'onepay_response' => $attempt->onepay_response,
                        ],
                    ];
                })->toArray(),
                'last_attempt' => $order->paymentAttempts->last() ? [
                    'id' => $order->paymentAttempts->last()->id,
                    'amount' => (float) $order->paymentAttempts->last()->amount,
                    'status' => $order->paymentAttempts->last()->status,
                ] : null,
                'cash_payment_logs' => $order->cashPaymentLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'amount_due' => (float) $log->amount_due,
                        'amount_received' => (float) $log->amount_received,
                        'change_amount' => (float) $log->change_amount,
                        'receipt_code' => $log->receipt_code,
                        'confirmed_at' => $log->confirmed_at,
                        'cashier_user_id' => $log->cashier_user_id,
                        'cashier' => $log->cashier ? [
                            'id' => $log->cashier->id,
                            'name' => $log->cashier->name,
                            'email' => $log->cashier->email,
                        ] : null,
                    ];
                })->toArray(),
            ],

            // Post-Purchase
            'post_purchase' => [
                'invoice' => $order->invoice ? [
                    'id' => $order->invoice->id,
                    'number' => $order->invoice->invoice_number,
                    'issued_at' => $order->invoice->issued_at,
                    'file_path' => $order->invoice->file_path,
                ] : null,
                'ticket_issuances' => $order->ticketIssuances->map(function ($issuance) {
                    return [
                        'id' => $issuance->id,
                        'qr_code' => $issuance->qr_code,
                        'issued_at' => $issuance->issued_at,
                    ];
                })->toArray(),
                'refund_requests' => $order->refundRequests->map(function ($refund) {
                    return [
                        'id' => $refund->id,
                        'amount' => (float) $refund->amount,
                        'status' => $refund->status,
                        'reason' => $refund->reason,
                        'created_at' => $refund->created_at,
                    ];
                })->toArray(),
            ],

            // Metadata
            'metadata' => $order->metadata ?? [],
        ];
    }

    /**
     * Cancel an order safely with validation
     */
    public function cancelOrder(Order $order, string $reason = ''): bool
    {
        // Validation: cannot cancel already paid/refunded orders
        if (in_array($order->status, ['PAID', 'paid', 'REFUNDED', 'refunded'], true)) {
            throw new \Exception("Cannot cancel order with status {$order->status}. Must refund instead.");
        }

        // Cannot cancel already cancelled orders
        if (in_array($order->status, ['CANCELLED', 'cancelled'], true)) {
            return false;
        }

        return $order->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'cancelled_by_admin' => true,
                'cancellation_reason' => $reason,
                'cancelled_at_admin' => now()->toISOString(),
                'cancelled_by_user_id' => auth()->id(),
            ]),
        ]);
    }

    /**
     * Mark unpaid order as paid (manual payment marking)
     */
    public function markOrderAsPaid(Order $order, string $notes = ''): bool
    {
        // Validation: only unpaid orders can be marked as paid
        if (!in_array($order->status, ['NEW', 'PENDING', 'EXPIRED'])) {
            throw new \Exception("Cannot mark order with status {$order->status} as paid.");
        }

        // Cannot mark expired order without creating new attempt
        if ($order->status === 'EXPIRED' && empty($notes)) {
            throw new \Exception("Expired orders must be recreated. Cannot mark expired order as paid directly.");
        }

        return $order->update([
            'status' => 'PAID',
            'paid_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'manual_payment_marking' => true,
                'marked_paid_at' => now()->toISOString(),
                'marked_by_user_id' => auth()->id(),
                'manual_payment_notes' => $notes,
            ]),
        ]);
    }

    /**
     * Create a refund request
     */
    public function createRefundRequest(Order $order, float $amount, string $reason = ''): RefundRequest
    {
        // Validation: only paid orders can be refunded
        if (!in_array($order->status, ['PAID', 'paid'], true)) {
            throw new \Exception("Can only refund PAID orders. Current status: {$order->status}");
        }

        // Prevent duplicate refund
        $existingRefund = $order->refundRequests()
            ->whereIn('status', ['PENDING', 'APPROVED', 'COMPLETED'])
            ->first();

        if ($existingRefund) {
            throw new \Exception("Order already has an active refund request.");
        }

        // Validate amount
        if ($amount <= 0 || $amount > $order->total_amount) {
            throw new \Exception("Invalid refund amount. Must be between 0 and {$order->total_amount}");
        }

        return $order->refundRequests()->create([
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'PENDING',
            'requested_by' => auth()->id(),
        ]);
    }

    /**
     * Get order summary statistics
     */
    public function getOrderStats(array $filters = []): array
    {
        $query = $this->buildFilterQuery($filters);

        $totalCount = $query->count();
        $paidCount = (clone $query)->whereIn('status', ['PAID', 'paid'])->count();
        $unpaidCount = (clone $query)->whereIn('status', ['NEW', 'PENDING', 'unpaid', 'pending_payment', 'pending_cash'])->count();
        $refundedCount = (clone $query)->whereIn('status', ['REFUNDED', 'refunded'])->count();
        $cancelledCount = (clone $query)->whereIn('status', ['CANCELLED', 'cancelled'])->count();

        $totalRevenue = (clone $query)->whereIn('status', ['PAID', 'paid'])->sum('total_amount');
        $totalValue = (clone $query)->sum('total_amount');

        return [
            'total_orders' => $totalCount,
            'paid_orders' => $paidCount,
            'unpaid_orders' => $unpaidCount,
            'refunded_orders' => $refundedCount,
            'cancelled_orders' => $cancelledCount,
            'total_revenue' => (float) $totalRevenue,
            'total_value' => (float) $totalValue,
            'payment_success_rate' => $totalCount > 0
                ? round(($paidCount / $totalCount) * 100, 2)
                : 0,
            'avg_order_value' => $totalCount > 0
                ? round($totalValue / $totalCount, 2)
                : 0,
        ];
    }

    /**
     * Get top promo codes
     */
    public function getTopPromoCodes(int $limit = 10, array $filters = []): Collection
    {
        $query = $this->buildFilterQuery($filters);

        return $query->whereIn('status', ['PAID', 'paid'])
            ->whereNotNull('promo_code_id')
            ->select('promo_code_id')
            ->selectRaw('COUNT(*) as usage_count')
            ->selectRaw('SUM(discount_amount) as total_discount')
            ->groupBy('promo_code_id')
            ->with('promoCode')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by event summary
     */
    public function getOrdersByEventSummary(array $filters = []): Collection
    {
        $query = $this->buildFilterQuery($filters);

        return $query->selectRaw('event_id')
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(CASE WHEN status = "PAID" THEN total_amount ELSE 0 END) as revenue')
            ->selectRaw('SUM(CASE WHEN status IN ("NEW", "PENDING") THEN total_amount ELSE 0 END) as pending_value')
            ->groupBy('event_id', 'status')
            ->with('event')
            ->get();
    }
}
