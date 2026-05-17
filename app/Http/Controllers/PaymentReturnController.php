<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use App\Services\Videc\PaymentService;
use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function __invoke(Request $request)
    {
        $txnRef = $request->input('vpc_MerchTxnRef')
            ?: $request->input('merchant_txn_ref')
            ?: $request->input('payment_attempt_ref');

        if (!$txnRef) {
            return response()->view('payment.return', $this->makeViewData(
                state: 'missing_reference',
                title: 'Không tìm thấy giao dịch',
                message: 'Trang thanh toán không nhận được mã tham chiếu giao dịch.',
            ), 400);
        }

        $attempt = PaymentAttempt::query()
            ->where('merchant_txn_ref', $txnRef)
            ->with(['order.registration.portalUser', 'order.invoice', 'order.registrationItems.ticket'])
            ->first();

        if (!$attempt) {
            return response()->view('payment.return', $this->makeViewData(
                state: 'not_found',
                title: 'Không tìm thấy giao dịch',
                message: 'Hệ thống chưa ghi nhận mã giao dịch này. Vui lòng kiểm tra lại hoặc liên hệ bộ phận hỗ trợ.',
                txnRef: $txnRef,
            ), 404);
        }

        $attempt = $this->paymentService->recordReturn($request->all());
        $attempt->loadMissing(['order.registration.portalUser', 'order.invoice', 'order.registrationItems.ticket']);

        $isConfirmed = $request->input('vpc_TxnResponseCode') === '0'
            || $attempt->status === 'success'
            || $attempt->order?->status === 'paid';

        return response()->view('payment.return', $this->makeViewData(
            state: $isConfirmed ? 'success' : ($attempt->status === 'pending_reconcile' ? 'pending_reconcile' : 'received'),
            title: $isConfirmed ? 'Chúc mừng bạn đã thanh toán thành công' : 'Đã ghi nhận giao dịch thanh toán',
            message: $isConfirmed
                ? 'Vui lòng tắt cửa sổ này. Hệ thống sẽ tự động đóng tab sau vài giây và đồng bộ vé/hoá đơn trong nền.'
                : 'Hệ thống đang kiểm tra và đồng bộ trạng thái giao dịch. Vui lòng giữ trang này mở trong giây lát.',
            attempt: $attempt,
            txnRef: $txnRef,
        ));
    }

    private function makeViewData(
        string $state,
        string $title,
        string $message,
        ?PaymentAttempt $attempt = null,
        ?string $txnRef = null,
    ): array {
        $order = $attempt?->order;
        $registration = $order?->registration;
        $amount = (float) ($attempt?->amount ?? $order?->total_amount ?? 0);

        return [
            'state' => $state,
            'title' => $title,
            'message' => $message,
            'attempt' => $attempt,
            'order' => $order,
            'registration' => $registration,
            'txnRef' => $txnRef ?: $attempt?->merchant_txn_ref,
            'amountDisplay' => number_format($amount, 0, ',', '.') . ' VND',
            'countdownSeconds' => 8,
            'appName' => config('app.name', 'Laravel'),
        ];
    }
}
