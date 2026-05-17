<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Models\PaymentAttempt;
use App\Services\Videc\OnePayGatewayService;
use App\Services\Videc\PaymentService;
use Illuminate\Http\Request;

class OnePayController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly OnePayGatewayService $gateway,
    ) {
    }

    public function paymentReturn(Request $request)
    {
        $payload = $request->all();
        $attempt = $payload ? $this->paymentService->recordReturn($payload) : null;

        return $this->responseSuccess([
            'attempt' => $attempt?->load('order', 'registration'),
            'payload' => $payload,
        ], 'Payment return received');
    }

    public function ipn(Request $request)
    {
        $payload = $request->all();
        $result = $this->paymentService->handleIpn($payload);

        return response('responsecode=1&desc=confirm-success', 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function queryDr(Request $request)
    {
        $data = $request->validate([
            'payment_attempt_id' => ['nullable', 'integer', 'exists:payment_attempts,id'],
            'merchant_txn_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $attempt = null;

        if (!empty($data['payment_attempt_id'])) {
            $attempt = PaymentAttempt::query()->findOrFail($data['payment_attempt_id']);
        } elseif (!empty($data['merchant_txn_ref'])) {
            $attempt = PaymentAttempt::query()->where('merchant_txn_ref', $data['merchant_txn_ref'])->firstOrFail();
        }

        $response = $this->paymentService->queryDr($attempt);

        return $this->responseSuccess($response, 'QueryDR completed');
    }
}
