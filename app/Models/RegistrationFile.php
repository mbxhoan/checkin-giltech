<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationFile extends Model
{
    use HasFactory;

    public const STATUS_TEMP = 'temp';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REPLACED = 'replaced';
    public const STATUS_EXPIRED = 'expired';

    protected $table = 'registration_files';

    protected $fillable = [
        'file_id',
        'event_id',
        'portal_user_id',
        'registration_id',
        'client_id',
        'replaced_by_id',
        'field_key',
        'owner_email',
        'disk',
        'path',
        'original_name',
        'extension',
        'mime_type',
        'size_bytes',
        'sha256',
        'status',
        'uploaded_by_type',
        'uploaded_by_id',
        'uploaded_at',
        'attached_at',
        'replaced_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'event_id' => 'int',
        'portal_user_id' => 'int',
        'registration_id' => 'int',
        'client_id' => 'int',
        'replaced_by_id' => 'int',
        'uploaded_by_id' => 'int',
        'size_bytes' => 'int',
        'uploaded_at' => 'datetime',
        'attached_at' => 'datetime',
        'replaced_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function replacedBy()
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }

    public function replacedFile()
    {
        return $this->hasOne(self::class, 'replaced_by_id');
    }

    public function logs()
    {
        return $this->hasMany(RegistrationFileLog::class);
    }
}
