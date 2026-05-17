<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalUser extends Model
{
    use HasFactory;

    protected $table = 'portal_users';

    public const DEFAULT_PASSWORD = '12345678';

    protected $fillable = [
        'email',
        'name',
        'phone',
        'password',
        'status',
        'email_verified_at',
        'last_login_at',
        'metadata',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function paymentAttempts()
    {
        return $this->hasManyThrough(PaymentAttempt::class, Order::class);
    }

    public function registrationFiles()
    {
        return $this->hasMany(RegistrationFile::class);
    }
}
