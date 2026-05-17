<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'portal_user_id',
        'event_id',
        'current_order_id',
        'code',
        'status',
        'checkin_sync_status',
        'checkin_reference',
        'submitted_at',
        'paid_at',
        'cancelled_at',
        'refunded_at',
        'checkin_synced_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'checkin_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function currentOrder()
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function items()
    {
        return $this->hasMany(RegistrationItem::class);
    }

    public function registrationFiles()
    {
        return $this->hasMany(RegistrationFile::class);
    }
}
