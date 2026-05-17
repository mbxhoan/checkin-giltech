<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationItem extends Model
{
    use HasFactory;

    protected $table = 'registration_items';

    protected $fillable = [
        'registration_id',
        'order_id',
        'ticket_id',
        'ticket_code',
        'ticket_name',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_amount',
        'status',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'int',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticketIssuances()
    {
        return $this->hasMany(TicketIssuance::class);
    }
}
