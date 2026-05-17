<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'event_id',
        'actor_type',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'level',
        'message',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'actor_id' => 'int',
        'subject_id' => 'int',
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
