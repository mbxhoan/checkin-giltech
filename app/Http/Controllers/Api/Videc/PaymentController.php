<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\Videc\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function createAttempt(Request $request, Order $order)
    {
        return $this->responseSuccess(
            $this->makePaymentAttempt($request, $order),
            'Payment URL generated'
        );
    }

    public function createAttemptLegacy(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'client_ip' => ['nullable', 'ip'],
        ]);

        $order = Order::query()->findOrFail($data['order_id']);

        return $this->responseSuccess(
            $this->makePaymentAttempt($request, $order, $data['client_ip'] ?? null),
            'Payment URL generated'
        );
    }

    public function applyPromo(Request $request, Order $order)
    {
        $data = $request->validate([
            'promo_code' => ['required', 'string', 'max:100'],
        ]);

        return $this->responseSuccess(
            $this->paymentService->applyPromo($order, $data['promo_code']),
            'Promo applied'
        );
    }

    public function removePromo(Order $order)
    {
        return $this->responseSuccess(
            $this->paymentService->removePromo($order),
            'Promo removed'
        );
    }

    public function portalOrder(Order $order)
    {
        return $this->responseSuccess($this->paymentService->getOrderSnapshot($order), 'Order snapshot');
    }

    public function repay(Request $request, Order $order)
    {
        $result = $this->paymentService->createAttempt($order, $request->ip());

        return $this->responseSuccess($result, 'Payment URL generated');
    }

    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->responseSuccess($this->paymentService->cancel($order, $data), 'Order cancelled');
    }

    public function refund(Request $request, Order $order)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->responseSuccess($this->paymentService->refund($order, $data), 'Refund requested');
    }

    public function changeTicket(Request $request, Order $order)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'registration_item_id' => ['nullable', 'integer', 'exists:registration_items,id'],
            'target_ticket_id' => ['nullable', 'integer', 'exists:tickets,id'],
        ]);

        return $this->responseSuccess($this->paymentService->changeTicket($order, $data), 'Ticket change requested');
    }

    public function showAttempt(PaymentAttempt $attempt)
    {
        return $this->responseSuccess($this->paymentService->getAttemptSnapshot($attempt), 'Payment attempt');
    }

    private function makePaymentAttempt(Request $request, Order $order, ?string $clientIp = null): array
    {
        $data = $request->validate([
            'client_ip' => ['nullable', 'ip'],
        ]);

        return $this->paymentService->createAttempt(
            $order,
            $clientIp ?? $data['client_ip'] ?? $request->ip()
        );
    }
}
