<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashPaymentLog extends Model
{
    use HasFactory;

    protected $table = 'cash_payment_logs';

    protected $fillable = [
        'event_id',
        'order_id',
        'payment_attempt_id',
        'cashier_user_id',
        'amount_due',
        'amount_received',
        'change_amount',
        'receipt_code',
        'note',
        'confirmed_at',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
