<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationFileLog extends Model
{
    use HasFactory;

    protected $table = 'registration_file_logs';

    protected $fillable = [
        'registration_file_id',
        'event_id',
        'portal_user_id',
        'action',
        'actor_type',
        'actor_ref',
        'message',
        'metadata',
    ];

    protected $casts = [
        'registration_file_id' => 'int',
        'event_id' => 'int',
        'portal_user_id' => 'int',
        'metadata' => 'array',
    ];

    public function registrationFile()
    {
        return $this->belongsTo(RegistrationFile::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class);
    }
}
