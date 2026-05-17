<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    use HasFactory;

    protected $table = 'promo_code_usages';

    protected $fillable = [
        'promo_code_id',
        'order_id',
        'registration_id',
        'portal_user_id',
        'discount_amount',
        'applied_at',
        'metadata',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class);
    }
}
