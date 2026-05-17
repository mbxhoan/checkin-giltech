<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'order_id',
        'registration_id',
        'invoice_no',
        'invoice_series',
        'status',
        'amount',
        'currency',
        'issued_at',
        'sent_at',
        'voided_at',
        'file_path',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'sent_at' => 'datetime',
        'voided_at' => 'datetime',
        'payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
