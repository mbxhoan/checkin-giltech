<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $fillable = [
        'order_id',
        'registration_id',
        'requested_by_portal_user_id',
        'amount',
        'status',
        'reason',
        'external_ref',
        'requested_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
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

    public function requestedByPortalUser()
    {
        return $this->belongsTo(PortalUser::class, 'requested_by_portal_user_id');
    }
}
