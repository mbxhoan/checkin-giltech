<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $table = 'payment_attempts';

    protected $fillable = [
        'order_id',
        'registration_id',
        'attempt_no',
        'gateway',
        'payment_method',
        'merchant_txn_ref',
        'onepay_transaction_no',
        'amount',
        'currency',
        'status',
        'response_code',
        'response_message',
        'secure_hash_valid',
        'amount_valid',
        'merchant_valid',
        'order_info_valid',
        'order_state_valid',
        'return_url',
        'callback_url',
        'expires_at',
        'redirected_at',
        'returned_at',
        'ipn_received_at',
        'succeeded_at',
        'failed_at',
        'expired_at',
        'cancelled_at',
        'failure_reason',
        'return_payload',
        'callback_payload',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'redirected_at' => 'datetime',
        'returned_at' => 'datetime',
        'ipn_received_at' => 'datetime',
        'succeeded_at' => 'datetime',
        'failed_at' => 'datetime',
        'expired_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'secure_hash_valid' => 'bool',
        'amount_valid' => 'bool',
        'merchant_valid' => 'bool',
        'order_info_valid' => 'bool',
        'order_state_valid' => 'bool',
        'return_payload' => 'array',
        'callback_payload' => 'array',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function cashPaymentLog()
    {
        return $this->hasOne(CashPaymentLog::class);
    }
}
