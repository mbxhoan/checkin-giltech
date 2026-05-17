<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientTicket extends BaseModel
{
    use HasFactory;

    protected $table = 'client_tickets';

    protected $casts = [
        'event_id' => 'int',
        'client_id' => 'int',
        'ticket_id' => 'int',
        'is_link' => 'bool',
    ];

    protected $fillable = [
        'event_id',
        'client_id',
        'ticket_id',
        'is_link',
        'img_path',
        'order_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
