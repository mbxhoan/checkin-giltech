<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends BaseModel
{
    use HasFactory;

    protected $table = 'tickets';

    protected $casts = [
        'card_id' => 'int',
        'sort_order' => 'int',
        'dates_valid' => 'array',
        'metadata' => 'array',
    ];

    protected $fillable = [
        'card_id',
        'event_code',
        'sort_order',
        'code',
        'name',
        'type',
        'price',
        'dates_string',
        'dates_valid',
        'metadata',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
