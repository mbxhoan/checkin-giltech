<?php

namespace App\Services\Videc;

use App\Models\CashPaymentLog;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Registration;
use App\Models\RegistrationItem;
use App\Models\Ticket;
use App\Models\TicketIssuance;
use App\Models\User;
use App\Services\Middleware\EmailService as MiddlewareEmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentService
{
    public function __construct(
        private readonly OnePayGatewayService $gateway,
        private readonly MiddlewareEmailService $emailService,
    ) {
    }

    public function createAttempt(Order $order, ?string $clientIp = null): array
    {
        return DB::transaction(function () use ($order, $clientIp) {
            $order->refresh();

            if ($order->payment_method === 'cash_at_event' || $order->status === 'pending_cash') {
                throw ValidationException::withMessages([
                    'order' => 'Cash orders cannot create OnePay payment attempts',
                ]);
            }

            if (!in_array($order->status, ['unpaid', 'pending_payment'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Order is not payable',
                ]);
            }

            if ((float) ($order->total_amount ?: $order->price) < 0) {
                throw ValidationException::withMessages([
                    'order' => 'Order amount is invalid',
                ]);
            }

            $attemptNo = ((int) PaymentAttempt::query()->where('order_id', $order->id)->max('attempt_no')) + 1;
            $attempt = PaymentAttempt::query()->create([
                'order_id' => $order->id,
                'registration_id' => $order->registration_id,
                'attempt_no' => $attemptNo,
                'gateway' => 'onepay',
                'merchant_txn_ref' => $this->makeTxnRef($order, $attemptNo),
                'amount' => $order->total_amount ?: $order->price,
                'currency' => $order->currency ?: config('onepay.currency', 'VND'),
                'status' => 'created',
                'return_url' => config('onepay.return_url'),
                'callback_url' => config('onepay.callback_url'),
                'expires_at' => now()->addMinutes((int) config('onepay.order_expiry_minutes', 15)),
                'metadata' => [
                    'order_no' => $order->no,
                ],
            ]);

            $payment = $this->gateway->paymentUrl($order, $attempt, $clientIp ?: request()->ip() ?: config('onepay.ticket_no', '127.0.0.1'));

            $attempt->forceFill([
                'status' => 'redirected',
                'redirected_at' => now(),
                'return_payload' => $payment['params'],
            ])->save();

            $order->forceFill([
                'token' => $attempt->merchant_txn_ref,
                'payment_url' => $payment['url'],
                'status' => 'pending_payment',
                'payment_method' => 'onepay',
                'expiry_date' => $attempt->expires_at,
            ])->save();

            $this->audit($order, 'payment_attempt_created', 'info', [
                'payment_attempt_id' => $attempt->id,
                'merchant_txn_ref' => $attempt->merchant_txn_ref,
            ]);

            return [
                'attempt' => $attempt->fresh(),
                'payment_url' => $payment['url'],
                'payment_params' => $payment['params'],
            ];
        });
    }

    public function createManualCashAttempt(Order $order, ?string $source = null): PaymentAttempt
    {
        return DB::transaction(function () use ($order, $source) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!in_array($order->status, ['unpaid', 'pending_payment', 'EXPIRED', 'expired'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Order is not eligible for manual cash confirmation',
                ]);
            }

            $existingAttempt = $order->paymentAttempts()
                ->where('payment_method', 'cash_at_event')
                ->latest('id')
                ->first();

            if ($existingAttempt) {
                return $existingAttempt->fresh(['order', 'registration', 'cashPaymentLog']);
            }

            $attemptNo = ((int) PaymentAttempt::query()->where('order_id', $order->id)->max('attempt_no')) + 1;
            $attempt = PaymentAttempt::query()->create([
                'order_id' => $order->id,
                'registration_id' => $order->registration_id,
                'attempt_no' => $attemptNo,
                'gateway' => 'manual',
                'payment_method' => 'cash_at_event',
                'merchant_txn_ref' => $this->makeManualTxnRef($order, $attemptNo),
                'amount' => $order->total_amount ?: $order->price,
                'currency' => $order->currency ?: config('onepay.currency', 'VND'),
                'status' => 'pending_cash',
                'expires_at' => $order->expiry_date,
                'metadata' => [
                    'order_no' => $order->no,
                    'source' => $source ?? 'registration',
                    'payment_method' => 'cash_at_event',
                ],
            ]);

            $this->audit($order, 'cash_payment_pending', 'info', [
                'payment_attempt_id' => $attempt->id,
                'merchant_txn_ref' => $attempt->merchant_txn_ref,
                'source' => $source ?? 'registration',
            ]);

            return $attempt->fresh(['order', 'registration', 'cashPaymentLog']);
        });
    }

    public function confirmCashPayment(Order $order, array $payload, User $cashier): array
    {
        $paymentSuccessOrder = null;
        $result = DB::transaction(function () use ($order, $payload, $cashier, &$paymentSuccessOrder) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'paid') {
                $cashPaymentLog = $order->cashPaymentLogs()->latest('id')->first();
                $attempt = $order->paymentAttempts()->where('payment_method', 'cash_at_event')->latest('id')->first();

                return [
                    'order' => $order->fresh(['registration.portalUser', 'registration.items', 'paymentAttempts', 'invoice', 'cashPaymentLogs', 'ticketIssuances']),
                    'cash_payment_log' => $cashPaymentLog,
                    'attempt' => $attempt?->fresh(['order', 'registration', 'cashPaymentLog']),
                ];
            }

            if (!in_array($order->status, ['unpaid', 'pending_payment', 'EXPIRED', 'expired'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Only unpaid, pending payment, or expired orders can be confirmed as cash',
                ]);
            }

            $amountDue = (float) ($order->total_amount ?: $order->price);
            $amountReceived = (float) ($payload['amount_received'] ?? 0);

            if ($amountReceived <= 0) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received is required',
                ]);
            }

            if ($amountReceived < $amountDue) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received must be greater than or equal to the order total',
                ]);
            }

            $receiptCode = trim((string) ($payload['receipt_code'] ?? ''));
            if ($receiptCode !== '') {
                $duplicateReceipt = CashPaymentLog::query()
                    ->where('receipt_code', $receiptCode)
                    ->where('order_id', '!=', $order->id)
                    ->exists();

                if ($duplicateReceipt) {
                    throw ValidationException::withMessages([
                        'receipt_code' => 'Receipt code already exists',
                    ]);
                }
            }

            $attempt = $order->paymentAttempts()
                ->where('payment_method', 'cash_at_event')
                ->latest('id')
                ->first();

            if (!$attempt) {
                $attempt = $this->createManualCashAttempt($order, 'cashier_confirmation');
                $attempt->refresh();
            }

            $existingLog = $order->cashPaymentLogs()->whereNull('voided_at')->first();
            if ($existingLog) {
                return [
                    'order' => $order->fresh(['registration.portalUser', 'registration.items', 'paymentAttempts', 'invoice', 'cashPaymentLogs', 'ticketIssuances']),
                    'cash_payment_log' => $existingLog,
                    'attempt' => $attempt->fresh(['order', 'registration', 'cashPaymentLog']),
                ];
            }

            $changeAmount = max(0, round($amountReceived - $amountDue, 2));
            $confirmedAt = now();

            $log = CashPaymentLog::query()->create([
                'event_id' => $order->event_id,
                'order_id' => $order->id,
                'payment_attempt_id' => $attempt->id,
                'cashier_user_id' => $cashier->id,
                'amount_due' => $amountDue,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'receipt_code' => $receiptCode ?: null,
                'note' => $payload['note'] ?? null,
                'confirmed_at' => $confirmedAt,
            ]);

            $attempt->forceFill([
                'status' => 'success',
                'succeeded_at' => $confirmedAt,
                'payment_method' => 'cash_at_event',
                'callback_payload' => array_merge($attempt->callback_payload ?? [], [
                    'cash_payment_log_id' => $log->id,
                    'cashier_user_id' => $cashier->id,
                    'amount_received' => $amountReceived,
                    'receipt_code' => $receiptCode ?: null,
                    'note' => $payload['note'] ?? null,
                    'confirmed_at' => $confirmedAt->toISOString(),
                ]),
                'metadata' => array_merge($attempt->metadata ?? [], [
                    'cash_payment_log_id' => $log->id,
                    'cashier_user_id' => $cashier->id,
                ]),
            ])->save();

            $order->forceFill([
                'status' => 'paid',
                'paid_at' => $confirmedAt,
                'payment_method' => 'cash_at_event',
                'metadata' => array_merge($order->metadata ?? [], [
                    'cash_payment_log_id' => $log->id,
                    'cashier_user_id' => $cashier->id,
                    'cash_payment_receipt_code' => $receiptCode ?: null,
                    'cash_payment_confirmed_at' => $confirmedAt->toISOString(),
                    'cash_payment_amount_received' => $amountReceived,
                ]),
                'ipn' => array_merge($order->ipn ?? [], [
                    'cash_payment' => [
                        'payment_attempt_id' => $attempt->id,
                        'cash_payment_log_id' => $log->id,
                        'cashier_user_id' => $cashier->id,
                        'amount_due' => $amountDue,
                        'amount_received' => $amountReceived,
                        'change_amount' => $changeAmount,
                        'receipt_code' => $receiptCode ?: null,
                        'confirmed_at' => $confirmedAt->toISOString(),
                    ],
                ]),
            ])->save();

            $registration = $order->registration;
            if ($registration) {
                $registration->forceFill([
                    'status' => 'paid',
                    'paid_at' => $confirmedAt,
                ])->save();
            }

            $this->issueInvoice($order, $registration ?? null);
            $this->issueTickets($order);
            $this->recordEmailLog($order, 'payment_success');
            $this->queueCheckinSync($order, 'PAID');
            $this->audit($order, 'cash_payment_confirmed', 'info', [
                'payment_attempt_id' => $attempt->id,
                'cash_payment_log_id' => $log->id,
                'cashier_user_id' => $cashier->id,
                'amount_received' => $amountReceived,
                'receipt_code' => $receiptCode ?: null,
            ]);

            $paymentSuccessOrder = $this->ensureCashPaymentClient($order);

            return [
                'order' => $order->fresh(['registration.portalUser', 'registration.items', 'paymentAttempts', 'invoice', 'cashPaymentLogs', 'ticketIssuances']),
                'cash_payment_log' => $log->fresh(['cashier', 'paymentAttempt']),
                'attempt' => $attempt->fresh(['order', 'registration', 'cashPaymentLog']),
            ];
        });

        if ($paymentSuccessOrder) {
            $this->sendVidecPaymentSuccessEmail($paymentSuccessOrder);
        }

        return $result;
    }

    public function recordReturn(array $payload): PaymentAttempt
    {
        $attempt = $this->resolveAttempt($payload);

        $attempt->forceFill([
            'status' => $attempt->status === 'success' ? 'success' : 'returned',
            'returned_at' => now(),
            'return_payload' => $payload,
        ])->save();

        return $attempt->fresh(['order', 'registration']);
    }

    public function handleIpn(array $payload): array
    {
        $attempt = $this->resolveAttempt($payload);
        if ($attempt->gateway !== 'onepay') {
            throw new NotFoundHttpException('Payment attempt not found');
        }
        $existingSuccess = $attempt->status === 'success' || $attempt->order?->status === 'paid';

        if ($existingSuccess) {
            return [
                'attempt' => $attempt->fresh(['order', 'registration', 'order.registrationItems', 'order.invoice']),
                'success' => true,
                'duplicate' => true,
            ];
        }

        $attempt->forceFill([
            'ipn_received_at' => now(),
            'status' => 'ipn_received',
            'callback_payload' => $payload,
            'response_code' => (string) ($payload['vpc_TxnResponseCode'] ?? $payload['vpc_ResponseCode'] ?? ''),
            'response_message' => $payload['vpc_Message'] ?? null,
            'secure_hash_valid' => $this->gateway->verify($payload),
            'amount_valid' => $this->amountMatches($attempt, $payload),
            'merchant_valid' => $this->merchantMatches($payload),
            'order_info_valid' => $this->orderInfoMatches($attempt, $payload),
            'order_state_valid' => $this->orderIsPayable($attempt->order),
        ])->save();

        $responseCode = (string) ($payload['vpc_TxnResponseCode'] ?? $payload['vpc_ResponseCode'] ?? '');
        $isSuccess = $responseCode === '0'
            && $attempt->secure_hash_valid
            && $attempt->amount_valid
            && $attempt->merchant_valid
            && $attempt->order_info_valid
            && $attempt->order_state_valid;

        if ($isSuccess) {
            $this->markSuccess($attempt, $payload);
        } elseif ($responseCode === '0' && (!$attempt->secure_hash_valid || !$attempt->amount_valid || !$attempt->merchant_valid || !$attempt->order_info_valid || !$attempt->order_state_valid)) {
            $this->markPendingReconcile($attempt, $payload, 'verification_mismatch');
        } elseif (!$attempt->secure_hash_valid || !$attempt->amount_valid || !$attempt->order_state_valid) {
            $this->markFailed($attempt, $payload, 'verification_failed');
        } elseif (in_array($responseCode, ['1', '2', '3', '4', '5', '6', '7', '8'], true)) {
            $this->markFailed($attempt, $payload, 'gateway_declined');
        } else {
            $this->markFailed($attempt, $payload, 'unknown_status');
        }

        return [
            'attempt' => $attempt->fresh(['order', 'registration', 'order.registrationItems', 'order.invoice']),
            'success' => $isSuccess,
        ];
    }

    public function queryDr(PaymentAttempt $attempt): array
    {
        if ($attempt->gateway !== 'onepay') {
            throw ValidationException::withMessages([
                'attempt' => 'QueryDR is only available for OnePay payment attempts',
            ]);
        }

        return $this->gateway->queryDr($attempt);
    }

    public function getAttemptSnapshot(PaymentAttempt $attempt): array
    {
        $attempt->load(['order.registration.portalUser', 'registration', 'order.invoice', 'order.ticketIssuances', 'cashPaymentLog']);

        return [
            'attempt' => $attempt,
            'order' => $attempt->order,
            'registration' => $attempt->registration,
            'invoice' => $attempt->order?->invoice,
            'ticket_issuances' => $attempt->order?->ticketIssuances ?? collect(),
            'cash_payment_log' => $attempt->cashPaymentLog,
        ];
    }

    public function getOrderSnapshot(Order $order): array
    {
        $order->load(['registration.portalUser', 'registration.items', 'paymentAttempts', 'invoice', 'refundRequests', 'ticketIssuances', 'cashPaymentLogs']);

        return [
            'order' => $order,
            'payment_attempts' => $order->paymentAttempts,
            'registration' => $order->registration,
            'invoice' => $order->invoice,
            'refund_requests' => $order->refundRequests,
            'ticket_issuances' => $order->ticketIssuances,
            'cash_payment_logs' => $order->cashPaymentLogs,
        ];
    }

    public function applyPromo(Order $order, string $code): array
    {
        return DB::transaction(function () use ($order, $code) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!in_array($order->status, ['unpaid'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Promo can only be applied before payment starts',
                ]);
            }

            if ($order->paymentAttempts()->exists()) {
                throw ValidationException::withMessages([
                    'order' => 'Promo cannot be changed after a payment attempt is created',
                ]);
            }

            $promo = PromoCode::query()
                ->where('event_id', $order->event_id)
                ->whereRaw('LOWER(code) = ?', [Str::lower($code)])
                ->where('status', 'ACTIVE')
                ->first();

            if (!$promo) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Promo code is invalid',
                ]);
            }

            if ($promo->discount_type !== 'percentage') {
                throw ValidationException::withMessages([
                    'promo_code' => 'Promo code type is not supported',
                ]);
            }

            $now = now();
            if (($promo->starts_at && $promo->starts_at->isAfter($now)) || ($promo->ends_at && $promo->ends_at->isBefore($now))) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Promo code is not active',
                ]);
            }

            if ($promo->usage_limit !== null && $promo->usage_count >= $promo->usage_limit) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Promo code usage limit reached',
                ]);
            }

            $subtotal = (float) ($order->subtotal_amount ?: $order->price);
            if ($promo->min_order_amount !== null && $subtotal < (float) $promo->min_order_amount) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Order does not meet promo minimum amount',
                ]);
            }

            $discount = round($subtotal * ((float) $promo->discount_value / 100), 2);
            if ($promo->max_discount_amount !== null) {
                $discount = min($discount, (float) $promo->max_discount_amount);
            }

            $discount = max(0, min($discount, $subtotal));
            $total = max(0, $subtotal - $discount + (float) $order->tax_amount);

            $previousUsage = PromoCodeUsage::query()->where('order_id', $order->id)->first();
            if ($previousUsage && $previousUsage->promo_code_id !== $promo->id) {
                PromoCode::query()->whereKey($previousUsage->promo_code_id)->where('usage_count', '>', 0)->decrement('usage_count');
            }

            PromoCodeUsage::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'promo_code_id' => $promo->id,
                    'registration_id' => $order->registration_id,
                    'portal_user_id' => $order->portal_user_id,
                    'discount_amount' => $discount,
                    'applied_at' => now(),
                    'metadata' => [
                        'discount_type' => $promo->discount_type,
                        'discount_value' => (float) $promo->discount_value,
                        'subtotal_amount' => $subtotal,
                    ],
                ]
            );

            if (!$previousUsage || $previousUsage->promo_code_id !== $promo->id) {
                $promo->increment('usage_count');
            }

            $order->forceFill([
                'promo_code_id' => $promo->id,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'price' => $total,
                'metadata' => array_merge($order->metadata ?? [], [
                    'promo_snapshot' => [
                        'code' => $promo->code,
                        'discount_type' => $promo->discount_type,
                        'discount_value' => (float) $promo->discount_value,
                        'discount_amount' => $discount,
                        'applied_at' => now()->toISOString(),
                    ],
                ]),
            ])->save();

            $this->audit($order, 'promo_applied', 'info', [
                'promo_code_id' => $promo->id,
                'code' => $promo->code,
                'discount_amount' => $discount,
            ]);

            return [
                'order' => $order->fresh(['promoCode']),
                'promo_code' => $promo->fresh(),
                'discount_amount' => $discount,
                'total_amount' => $total,
            ];
        });
    }

    public function removePromo(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (!in_array($order->status, ['unpaid'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Promo can only be removed before payment starts',
                ]);
            }

            if ($order->paymentAttempts()->exists()) {
                throw ValidationException::withMessages([
                    'order' => 'Promo cannot be changed after a payment attempt is created',
                ]);
            }

            $usage = PromoCodeUsage::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            $subtotal = (float) ($order->subtotal_amount ?: $order->price);
            $tax = (float) ($order->tax_amount ?: 0);
            $total = max(0, $subtotal + $tax);

            if ($usage) {
                PromoCode::query()
                    ->whereKey($usage->promo_code_id)
                    ->where('usage_count', '>', 0)
                    ->decrement('usage_count');

                $usage->delete();
            }

            $metadata = $order->metadata ?? [];
            unset($metadata['promo_snapshot']);

            $order->forceFill([
                'promo_code_id' => null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $total,
                'price' => $total,
                'metadata' => $metadata,
            ])->save();

            $this->audit($order, 'promo_removed', 'info', [
                'promo_code_id' => $usage?->promo_code_id,
                'subtotal_amount' => $subtotal,
                'total_amount' => $total,
            ]);

            return [
                'order' => $order->fresh(['promoCode']),
                'promo_code' => null,
                'discount_amount' => 0,
                'total_amount' => $total,
            ];
        });
    }

    public function cancel(Order $order, array $payload = []): Order
    {
        $order->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'cancel_reason' => $payload['reason'] ?? null,
            ]),
        ])->save();

        if ($order->registration) {
            $order->registration->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ])->save();
        }

        $this->revokeTickets($order, 'cancelled');
        $this->recordEmailLog($order, 'order_cancelled');
        $this->queueCheckinSync($order, 'CANCELLED');
        $this->audit($order, 'order_cancelled', 'warning', $payload);

        return $order->fresh(['registration']);
    }

    public function refund(Order $order, array $payload = []): Order
    {
        $order->forceFill([
            'status' => 'refunded',
            'refunded_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'refund_reason' => $payload['reason'] ?? null,
            ]),
        ])->save();

        if ($order->registration) {
            $order->registration->forceFill([
                'status' => 'refunded',
                'refunded_at' => now(),
            ])->save();
        }

        $this->revokeTickets($order, 'refunded');
        $this->recordEmailLog($order, 'order_refunded');
        $this->queueCheckinSync($order, 'REFUNDED');
        $this->audit($order, 'order_refunded', 'warning', $payload);

        return $order->fresh(['registration']);
    }

    public function changeTicket(Order $order, array $payload = []): Order
    {
        DB::transaction(function () use ($order, $payload) {
            $order->refresh();
            $targetTicket = !empty($payload['target_ticket_id']) ? Ticket::query()->findOrFail($payload['target_ticket_id']) : null;
            $itemQuery = $order->registrationItems();

            if (!empty($payload['registration_item_id'])) {
                $itemQuery->whereKey($payload['registration_item_id']);
            }

            if ($targetTicket) {
                /** @var RegistrationItem $item */
                foreach ($itemQuery->get() as $item) {
                    $item->forceFill([
                        'ticket_id' => $targetTicket->id,
                        'ticket_code' => $targetTicket->code,
                        'ticket_name' => $targetTicket->name,
                        'metadata' => array_merge($item->metadata ?? [], [
                            'changed_from_ticket_id' => $item->ticket_id,
                            'changed_at' => now()->toISOString(),
                        ]),
                    ])->save();
                }
            }

            $this->revokeTickets($order, 'changed');

            if ($targetTicket && $order->status === 'paid') {
                $this->issueTickets($order->fresh());
            }

            $order->forceFill([
                'metadata' => array_merge($order->metadata ?? [], [
                    'ticket_change_request' => $payload,
                    'ticket_change_requested_at' => now()->toISOString(),
                ]),
            ])->save();
        });

        $this->recordEmailLog($order, 'ticket_changed');
        $this->queueCheckinSync($order, 'TICKET_CHANGED');
        $this->audit($order, 'ticket_change_requested', 'info', $payload);

        return $order->fresh(['registration', 'ticketIssuances']);
    }

    private function resolveAttempt(array $payload): PaymentAttempt
    {
        $txnRef = $payload['vpc_MerchTxnRef'] ?? $payload['merchant_txn_ref'] ?? $payload['merchantTxnRef'] ?? null;

        if (!$txnRef) {
            throw new NotFoundHttpException('Missing merchant transaction reference');
        }

        $attempt = PaymentAttempt::query()->where('merchant_txn_ref', $txnRef)->first();

        if (!$attempt) {
            throw new NotFoundHttpException('Payment attempt not found');
        }

        return $attempt->load('order', 'registration');
    }

    private function markSuccess(PaymentAttempt $attempt, array $payload): void
    {
        if ($attempt->status === 'success') {
            return;
        }

        $paymentSuccessOrder = null;

        DB::transaction(function () use ($attempt, $payload, &$paymentSuccessOrder) {
            $attempt->forceFill([
                'status' => 'success',
                'succeeded_at' => now(),
                'callback_payload' => $payload,
            ])->save();

            $order = $attempt->order()->lockForUpdate()->first();
            $registration = $attempt->registration;

            $order->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'onepay',
                'ipn' => $payload,
            ])->save();

            if ($registration) {
                $registration->forceFill([
                    'status' => 'paid',
                    'paid_at' => now(),
                ])->save();
            }

            $this->issueInvoice($order, $registration ?? null);
            $this->issueTickets($order);
            $this->recordEmailLog($order, 'payment_success');
            $this->queueCheckinSync($order, 'PAID');
            $this->audit($order, 'payment_success', 'info', [
                'payment_attempt_id' => $attempt->id,
                'merchant_txn_ref' => $attempt->merchant_txn_ref,
            ]);

            $paymentSuccessOrder = $order->fresh(['client.event', 'registration.portalUser']);
        });

        if ($paymentSuccessOrder) {
            $this->sendVidecPaymentSuccessEmail($paymentSuccessOrder);
        }
    }

    private function markPendingReconcile(PaymentAttempt $attempt, array $payload, string $reason): void
    {
        $attempt->forceFill([
            'status' => 'pending_reconcile',
            'failed_at' => null,
            'failure_reason' => $reason,
            'callback_payload' => $payload,
        ])->save();

        $attempt->order?->forceFill([
            'status' => 'pending_payment',
        ])->save();

        $this->audit($attempt->order, 'payment_pending_reconcile', 'warning', [
            'payment_attempt_id' => $attempt->id,
            'reason' => $reason,
            'secure_hash_valid' => $attempt->secure_hash_valid,
            'amount_valid' => $attempt->amount_valid,
            'merchant_valid' => $attempt->merchant_valid,
            'order_info_valid' => $attempt->order_info_valid,
            'order_state_valid' => $attempt->order_state_valid,
        ]);
    }

    private function markFailed(PaymentAttempt $attempt, array $payload, string $reason): void
    {
        $status = in_array((string) ($payload['vpc_TxnResponseCode'] ?? $payload['vpc_ResponseCode'] ?? ''), ['7', '8'], true) ? 'expired' : 'failed';

        $attempt->forceFill([
            'status' => $status,
            'failed_at' => now(),
            'expired_at' => $status === 'expired' ? now() : $attempt->expired_at,
            'failure_reason' => $reason,
        ])->save();

        if ($attempt->order) {
            $attempt->order->forceFill([
                'status' => 'unpaid',
            ])->save();
        }

        if ($status === 'expired') {
            $this->recordEmailLog($attempt->order, 'payment_expired');
        }

        $this->audit($attempt->order, 'payment_failed', 'warning', [
            'payment_attempt_id' => $attempt->id,
            'reason' => $reason,
        ]);
    }

    private function issueInvoice(Order $order, ?Registration $registration): void
    {
        Invoice::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'registration_id' => $registration?->id,
                'invoice_no' => $this->makeInvoiceNo($order),
                'invoice_series' => 'VIDEC2026',
                'status' => 'issued',
                'amount' => $order->total_amount ?: $order->price,
                'currency' => $order->currency ?: config('onepay.currency', 'VND'),
                'issued_at' => now(),
                'payload' => [
                    'order_no' => $order->no,
                ],
            ]
        );
    }

    private function issueTickets(Order $order): void
    {
        $order->loadMissing('registrationItems');

        foreach ($order->registrationItems as $item) {
            $quantity = max(1, (int) $item->quantity);

            for ($unit = 1; $unit <= $quantity; $unit++) {
                $ticketCode = $this->makeTicketCode($item, $unit);

                TicketIssuance::query()->firstOrCreate(
                    [
                        'registration_item_id' => $item->id,
                        'ticket_code' => $ticketCode,
                    ],
                    [
                        'order_id' => $order->id,
                        'ticket_id' => $item->ticket_id,
                        'qr_code' => (string) Str::uuid(),
                        'status' => 'issued',
                        'issued_at' => now(),
                        'payload' => [
                            'order_no' => $order->no,
                            'unit_index' => $unit,
                            'quantity' => $quantity,
                        ],
                    ]
                );
            }
        }
    }

    private function revokeTickets(Order $order, string $reason): void
    {
        TicketIssuance::query()
            ->where('order_id', $order->id)
            ->where('status', 'issued')
            ->get()
            ->each(function (TicketIssuance $issuance) use ($reason) {
                $issuance->forceFill([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                    'payload' => array_merge($issuance->payload ?? [], [
                        'revoked_reason' => $reason,
                        'revoked_at' => now()->toISOString(),
                    ]),
                ])->save();
            });
    }

    private function makeTicketCode(RegistrationItem $item, int $unit): string
    {
        $base = $item->ticket_code ?: 'TICKET';

        return sprintf('%s-%06d-%02d', Str::upper($base), $item->id, $unit);
    }

    private function makeInvoiceNo(Order $order): string
    {
        return sprintf('INV-%s-%06d', now()->format('Ymd'), $order->id);
    }

    private function makeTxnRef(Order $order, int $attemptNo): string
    {
        return sprintf('VIDEC-%06d-%02d-%s', $order->id, $attemptNo, Str::upper(Str::random(8)));
    }

    private function makeManualTxnRef(Order $order, int $attemptNo): string
    {
        return sprintf('CASH-%06d-%02d-%s', $order->id, $attemptNo, Str::upper(Str::random(8)));
    }

    private function ensureCashPaymentClient(Order $order): Order
    {
        $order->loadMissing('registration.portalUser', 'registration.event', 'event', 'client');

        if (!$order->registration?->portalUser) {
            return $order->fresh(['client.event', 'registration.portalUser']);
        }

        if ($order->client) {
            return $order->fresh(['client.event', 'registration.portalUser']);
        }

        $portalUser = $order->registration->portalUser;
        $event = $order->registration->event ?? $order->event;

        if (!$event || empty($portalUser->email)) {
            return $order->fresh(['client.event', 'registration.portalUser']);
        }

        $client = Client::query()->firstOrCreate(
            [
                'event_id' => $event->id,
                'email' => Str::lower((string) $portalUser->email),
            ],
            [
                'event_code' => $event->code,
                'qrcode' => $event->generateQrcodeOnSetting(
                    $event->code,
                    $portalUser->phone,
                    $portalUser->email,
                    $portalUser->name,
                    []
                ),
                'name' => $portalUser->name ?: $order->registration->name ?: $order->portalUser?->name ?: $portalUser->email,
                'phone' => $portalUser->phone,
                'status' => Client::STATUS_ACTIVE,
                'type' => Client::TYPE_NORMAL,
                'register_source' => Client::REGISTER_WEB,
            ]
        );

        if (empty($client->img_qrcode) || !Storage::exists("public/{$client->img_qrcode}")) {
            $generatedImgQrcode = $client->generateImgQrcode();

            if (!empty($generatedImgQrcode)) {
                $client->forceFill([
                    'img_qrcode' => $generatedImgQrcode,
                ])->save();
                $client->refresh();
            }
        }

        if ((int) $order->client_id !== (int) $client->id) {
            $order->forceFill([
                'client_id' => $client->id,
            ])->save();
        }

        return $order->fresh(['client.event', 'registration.portalUser']);
    }

    private function amountMatches(PaymentAttempt $attempt, array $payload): bool
    {
        $incoming = (int) ($payload['vpc_Amount'] ?? 0);

        return $incoming === $this->gateway->formatAmount($attempt->amount);
    }

    private function merchantMatches(array $payload): bool
    {
        return (string) ($payload['vpc_Merchant'] ?? '') === (string) config('onepay.merchant_id');
    }

    private function orderInfoMatches(PaymentAttempt $attempt, array $payload): bool
    {
        $incoming = (string) ($payload['vpc_OrderInfo'] ?? '');

        return $incoming !== '' && in_array($incoming, [(string) $attempt->order?->no, (string) $attempt->order?->id], true);
    }

    private function orderIsPayable(?Order $order): bool
    {
        if (!$order) {
            return false;
        }

        return in_array($order->status, ['unpaid', 'pending_payment'], true);
    }

    private function audit(?Order $order, string $action, string $level, array $metadata = []): void
    {
        AuditLog::query()->create([
            'event_id' => $order?->event_id,
            'actor_type' => 'system',
            'actor_id' => null,
            'action' => $action,
            'subject_type' => Order::class,
            'subject_id' => $order?->id,
            'level' => $level,
            'message' => $action,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    private function recordEmailLog(?Order $order, string $type): void
    {
        if (!$order) {
            return;
        }

        $order->loadMissing('registration.portalUser', 'event');
        $portalUser = $order->registration?->portalUser;

        EmailLog::query()->create([
            'event_id' => $order->event_id,
            'portal_user_id' => $order->portal_user_id,
            'registration_id' => $order->registration_id,
            'order_id' => $order->id,
            'type' => $type,
            'subject' => $this->emailSubject($type),
            'name' => $portalUser?->name,
            'email' => $portalUser?->email,
            'content' => $this->emailSubject($type),
            'status' => 'queued',
            'metadata' => [
                'from' => config('mail.from.address') ?: env('FROM_MAIL'),
                'order_no' => $order->no,
                'payment_method' => $order->payment_method,
            ],
        ]);
    }

    private function emailSubject(string $type): string
    {
        return match ($type) {
            'payment_success' => 'VIDEC 2026 payment confirmed',
            'payment_expired' => 'VIDEC 2026 payment session expired',
            'order_refunded' => 'VIDEC 2026 refund recorded',
            'order_cancelled' => 'VIDEC 2026 order cancelled',
            'ticket_changed' => 'VIDEC 2026 ticket changed',
            default => 'VIDEC 2026 registration update',
        };
    }

    private function sendVidecPaymentSuccessEmail(Order $order): void
    {
        try {
            $client = $order->client;

            if (!$client) {
                return;
            }

            $client->loadMissing('event');

            if ($client->event?->code !== 'videc-2026') {
                return;
            }

            if (empty($client->img_qrcode) || !Storage::exists("public/{$client->img_qrcode}")) {
                $generatedImgQrcode = $client->generateImgQrcode();

                if (!empty($generatedImgQrcode)) {
                    $client->forceFill([
                        'img_qrcode' => $generatedImgQrcode,
                    ])->save();
                    $client->refresh();
                }
            }

            if (empty($client->img_qrcode) || !Storage::exists("public/{$client->img_qrcode}")) {
                Log::warning('VIDEC 2026 payment success email skipped because qrcode image is missing', [
                    'order_id' => $order->id,
                    'client_id' => $client->id,
                ]);

                return;
            }

            $this->emailService->sendCampaignEmailByClient($client, 308, [
                'name' => $client->name,
                'qrcode' => $client->qrcode,
                'img_qrcode' => route('clients.view-qrcode-by-id', [
                    'id' => $client->id,
                ]),
            ]);
        } catch (\Throwable $th) {
            Log::warning('VIDEC 2026 payment success campaign email failed', [
                'order_id' => $order->id,
                'error' => $th->getMessage(),
            ]);
        }
    }

    private function queueCheckinSync(Order $order, string $paymentStatus): void
    {
        $order->loadMissing('invoice', 'registration');

        $payload = [
            'registration_id' => $order->registration_id,
            'portal_user_id' => $order->portal_user_id,
            'order_id' => $order->id,
            'payment_status' => $paymentStatus,
            'invoice_no' => $order->invoice?->invoice_no,
            'synced_at' => null,
        ];

        $metadata = array_merge($order->metadata ?? [], [
            'checkin_sync_payload' => $payload,
            'checkin_sync_queued_at' => now()->toISOString(),
        ]);

        $order->forceFill([
            'checkin_sync_status' => 'queued',
            'metadata' => $metadata,
        ])->save();

        if ($order->registration) {
            $order->registration->forceFill([
                'checkin_sync_status' => 'queued',
                'metadata' => array_merge($order->registration->metadata ?? [], [
                    'checkin_sync_payload' => $payload,
                ]),
            ])->save();
        }
    }
}
