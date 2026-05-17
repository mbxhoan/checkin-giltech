<?php

namespace App\Services\Videc;

use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OnePayGatewayService
{
    public function paymentUrl(Order $order, PaymentAttempt $attempt, string $clientIp): array
    {
        $params = [
            'vpc_AccessCode' => config('onepay.access_code'),
            'vpc_Amount' => $this->formatAmount($attempt->amount),
            'vpc_CallbackURL' => $attempt->callback_url ?: config('onepay.callback_url'),
            'vpc_Command' => config('onepay.command', 'pay'),
            'vpc_Currency' => $attempt->currency ?: config('onepay.currency', 'VND'),
            'vpc_Locale' => config('onepay.locale', 'vn'),
            'vpc_Merchant' => config('onepay.merchant_id'),
            'vpc_MerchTxnRef' => $attempt->merchant_txn_ref,
            'vpc_OrderInfo' => $order->no ?: (string) $order->id,
            'vpc_ReturnURL' => $attempt->return_url ?: config('onepay.return_url'),
            'vpc_TicketNo' => $clientIp ?: config('onepay.ticket_no', '127.0.0.1'),
            'vpc_TransactionNo' => '',
            'vpc_TransactionType' => config('onepay.transaction_type', '01'),
            'vpc_Version' => config('onepay.version', '2'),
        ];

        $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');
        $params['vpc_SecureHash'] = $this->sign($params);

        return [
            'endpoint' => config('onepay.payment_url'),
            'params' => $params,
            'url' => $this->buildUrl(config('onepay.payment_url'), $params),
        ];
    }

    public function queryDrPayload(PaymentAttempt $attempt): array
    {
        $params = [
            'vpc_AccessCode' => config('onepay.access_code'),
            'vpc_Command' => config('onepay.querydr_command', 'queryDR'),
            'vpc_Merchant' => config('onepay.merchant_id'),
            'vpc_MerchTxnRef' => $attempt->merchant_txn_ref,
            'vpc_User' => config('onepay.user'),
            'vpc_Version' => config('onepay.version', '2'),
        ];

        $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');
        $params['vpc_SecureHash'] = $this->sign($params);

        return $params;
    }

    public function queryDr(PaymentAttempt $attempt): array
    {
        $payload = $this->queryDrPayload($attempt);
        $timeout = max(3, (int) config('onepay.querydr_timeout_seconds', 10));
        $connectTimeout = max(1, (int) config('onepay.querydr_connect_timeout_seconds', 3));
        $retryTimes = max(0, (int) config('onepay.querydr_retry_times', 1));
        $retrySleepMs = max(0, (int) config('onepay.querydr_retry_sleep_milliseconds', 200));

        try {
            $request = Http::asForm()
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retryTimes, $retrySleepMs, function ($exception) {
                    $status = data_get($exception, 'response.status');

                    // Retry only transient failures.
                    return $status === null || $status >= 500;
                }, false);

            $response = $request->post(config('onepay.querydr_url'), $payload);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ];
        } catch (Throwable $exception) {
            Log::warning('OnePay QueryDR transport failure', [
                'payment_attempt_id' => $attempt->id,
                'merchant_txn_ref' => $attempt->merchant_txn_ref,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'error' => 'querydr_transport_failure',
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function verify(array $params): bool
    {
        $secureHash = strtoupper((string) Arr::get($params, 'vpc_SecureHash'));

        if ($secureHash === '') {
            return false;
        }

        return hash_equals($secureHash, $this->sign($params));
    }

    public function sign(array $params): string
    {
        $hashString = $this->buildHashString($params);
        $secret = (string) config('onepay.secure_secret');
        $key = $this->normalizeSecret($secret);

        return strtoupper(hash_hmac('sha256', $hashString, $key));
    }

    public function buildHashString(array $params): string
    {
        $filtered = [];

        foreach ($params as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (!preg_match('/^(vpc_|user_)/', $key)) {
                continue;
            }

            if ($key === 'vpc_SecureHash' || $key === 'vpc_SecureHashType') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        ksort($filtered, SORT_STRING);

        $pairs = [];

        foreach ($filtered as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        return implode('&', $pairs);
    }

    public function formatAmount($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function buildUrl(string $endpoint, array $params): string
    {
        return rtrim($endpoint, '?') . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function normalizeSecret(string $secret): string
    {
        $secret = trim($secret);

        if ($secret !== '' && ctype_xdigit($secret) && strlen($secret) % 2 === 0) {
            $decoded = @hex2bin($secret);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $secret;
    }
}
