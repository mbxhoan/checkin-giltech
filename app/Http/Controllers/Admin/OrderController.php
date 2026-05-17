<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\CancelRequest;
use App\Http\Requests\Admin\Orders\MarkPaidRequest;
use App\Http\Requests\Admin\Orders\RefundRequest;
use App\Http\Requests\Admin\Orders\ResendEmailRequest;
use App\Http\Resources\OrderResource;
use App\Models\Event;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\Admin\OrderService;
use App\Services\Middleware\EmailService;
use App\Services\Videc\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly EmailService $emailService,
        private readonly PaymentService $paymentService,
    ) {
    }

    /**
     * Display a listing of orders with filters
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'event_id',
            'status',
            'payment_status',
            'email',
            'code',
            'date_from',
            'date_to',
            'promo_code_id',
            'amount_from',
            'amount_to',
        ]);

        $orders = $this->orderService->getOrdersWithFilters($filters, 15);
        $stats = $this->orderService->getOrderStats($filters);

        // Get filter options
        $events = Event::query()
            ->whereIn('code', ['videc-2026'])
            ->orderByDesc('from_date')
            ->get();

        $promoCodes = PromoCode::query()
            ->active()
            ->orderBy('code')
            ->get();

        $statuses = [
            'NEW' => 'New',
            'PENDING' => 'Pending',
            'PAID' => 'Paid',
            'EXPIRED' => 'Expired',
            'CANCELLED' => 'Cancelled',
            'REFUNDED' => 'Refunded',
        ];

        $paymentStatuses = [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return view('admin.videc.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $filters,
            'events' => $events,
            'statuses' => $statuses,
            'paymentStatuses' => $paymentStatuses,
            'promoCodes' => $promoCodes,
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): View
    {
        $orderSnapshot = $this->orderService->getOrderSnapshot($order);
        $paymentAttempts = $order->paymentAttempts()
            ->latest()
            ->get();

        // Get all related data
        $order->load([
            'event',
            'portalUser',
            'registration.items.ticket',
            'promoCode',
            'paymentAttempts',
            'invoice',
            'ticketIssuances',
            'refundRequests',
            'registrationItems.ticket',
        ]);

        $canMarkPaid = in_array($order->status, ['NEW', 'PENDING', 'EXPIRED', 'expired', 'unpaid', 'pending_payment'], true);
        $canCancel = in_array($order->status, ['NEW', 'PENDING', 'unpaid', 'pending_payment'], true);
        $canRefund = in_array($order->status, ['PAID', 'paid'], true) && !$order->refundRequests()
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->exists();

        return view('admin.videc.orders.show', [
            'order' => $order,
            'orderSnapshot' => $orderSnapshot,
            'paymentAttempts' => $paymentAttempts,
            'canMarkPaid' => $canMarkPaid,
            'canCancel' => $canCancel,
            'canRefund' => $canRefund,
        ]);
    }

    /**
     * Mark an order as paid (manual payment marking)
     */
    public function markPaid(MarkPaidRequest $request, Order $order)
    {
        try {
            $this->paymentService->confirmCashPayment(
                $order,
                [
                    'amount_received' => $request->input('amount_received'),
                    'receipt_code' => $request->input('receipt_code'),
                    'note' => $request->input('notes'),
                ],
                $request->user()
            );

            return redirect()
                ->back()
                ->with('success', 'Cash payment confirmed successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an order
     */
    public function cancel(CancelRequest $request, Order $order)
    {
        try {
            $this->orderService->cancelOrder(
                $order,
                $request->input('reason', '')
            );

            return redirect()
                ->back()
                ->with('success', 'Order cancelled successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Create a refund request
     */
    public function refund(RefundRequest $request, Order $order)
    {
        try {
            $refundRequest = $this->orderService->createRefundRequest(
                $order,
                (float) $request->input('amount'),
                $request->input('reason', '')
            );

            return redirect()
                ->back()
                ->with('success', 'Refund request created successfully. ID: ' . $refundRequest->id);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Resend confirmation email
     */
    public function resendEmail(ResendEmailRequest $request, Order $order)
    {
        try {
            // Get email type to resend
            $emailType = $request->input('email_type', 'confirmation');

            // Send appropriate email based on type
            if ($emailType === 'confirmation') {
                // Send payment confirmation email
                $this->emailService->sendPaymentConfirmation($order);
            } elseif ($emailType === 'invoice' && $order->invoice) {
                // Send invoice email
                $this->emailService->sendInvoice($order);
            }

            return redirect()
                ->back()
                ->with('success', 'Email sent successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Export orders to CSV (API endpoint)
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'event_id',
            'status',
            'payment_status',
            'email',
            'code',
            'date_from',
            'date_to',
            'promo_code_id',
            'amount_from',
            'amount_to',
        ]);

        $orders = $this->orderService->getOrdersWithFilters($filters, 10000);

        $csv = "Order Code,Status,Email,Event,Customer,Total Amount,Paid At,Created At\n";

        foreach ($orders as $order) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s",%f,"%s","%s"' . "\n",
                $order->code,
                $order->status,
                $order->portalUser?->email ?? '',
                $order->event?->name ?? '',
                $order->registration?->name ?? '',
                $order->total_amount,
                $order->paid_at?->format('Y-m-d H:i:s') ?? '',
                $order->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders-' . now()->format('Y-m-d-His') . '.csv"',
        ]);
    }
}
