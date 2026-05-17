<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'ref_id',
        'no',
        'code',
        'token',
        'payment_url',
        'price',
        'expiry_date',
        'ipn',
        'status',
        'portal_user_id',
        'registration_id',
        'event_id',
        'promo_code_id',
        'payment_method',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_at',
        'cancelled_at',
        'refunded_at',
        'checkin_sync_status',
        'checkin_reference',
        'metadata',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'ipn' => 'array',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function registrationItems()
    {
        return $this->hasMany(RegistrationItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function ticketIssuances()
    {
        return $this->hasMany(TicketIssuance::class);
    }

    public function cashPaymentLogs()
    {
        return $this->hasMany(CashPaymentLog::class);
    }
}
