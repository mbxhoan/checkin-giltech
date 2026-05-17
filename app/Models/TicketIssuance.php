<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketIssuance extends Model
{
    use HasFactory;

    protected $table = 'ticket_issuances';

    protected $fillable = [
        'registration_item_id',
        'order_id',
        'client_ticket_id',
        'ticket_id',
        'ticket_code',
        'qr_code',
        'status',
        'issued_at',
        'revoked_at',
        'payload',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
        'payload' => 'array',
    ];

    public function registrationItem()
    {
        return $this->belongsTo(RegistrationItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function clientTicket()
    {
        return $this->belongsTo(ClientTicket::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
