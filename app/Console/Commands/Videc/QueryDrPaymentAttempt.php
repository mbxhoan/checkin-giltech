<?php

namespace App\Console\Commands\Videc;

use App\Models\PaymentAttempt;
use App\Services\Videc\PaymentService;
use Illuminate\Console\Command;

class QueryDrPaymentAttempt extends Command
{
    protected $signature = 'videc:querydr
        {--payment_attempt_id= : Payment attempt ID to query}
        {--merchant_txn_ref= : Merchant transaction reference to query}
        {--pending : Query pending/reconcile attempts in bulk}
        {--limit=25 : Maximum pending attempts to query in one run}';

    protected $description = 'Run OnePay QueryDR for a payment attempt and print the raw response.';

    public function __construct(private readonly PaymentService $paymentService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $paymentAttemptId = $this->option('payment_attempt_id');
        $merchantTxnRef = $this->option('merchant_txn_ref');
        $pending = (bool) $this->option('pending');

        if ($pending) {
            return $this->handlePending();
        }

        if (!$paymentAttemptId && !$merchantTxnRef) {
            $this->error('Provide either --payment_attempt_id or --merchant_txn_ref.');

            return Command::FAILURE;
        }

        $attempt = null;

        if ($paymentAttemptId) {
            $attempt = PaymentAttempt::query()->findOrFail($paymentAttemptId);
        } else {
            $attempt = PaymentAttempt::query()->where('merchant_txn_ref', $merchantTxnRef)->firstOrFail();
        }

        $response = $this->paymentService->queryDr($attempt);

        $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }

    private function handlePending(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $attempts = PaymentAttempt::query()
            ->whereIn('status', ['redirected', 'returned', 'ipn_received', 'pending_reconcile'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', now());
            })
            ->oldest()
            ->limit($limit)
            ->get();

        if ($attempts->isEmpty()) {
            $this->info('No pending payment attempts found.');

            return Command::SUCCESS;
        }

        foreach ($attempts as $attempt) {
            $response = $this->paymentService->queryDr($attempt);
            $attempt->forceFill([
                'metadata' => array_merge($attempt->metadata ?? [], [
                    'last_querydr_response' => $response,
                    'last_querydr_at' => now()->toISOString(),
                ]),
            ])->save();

            $this->line($attempt->merchant_txn_ref . ': ' . json_encode($response, JSON_UNESCAPED_SLASHES));
        }

        return Command::SUCCESS;
    }
}
