<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiClientLog extends Model
{
    use HasFactory;

    protected $table = 'api_client_logs';

    protected $fillable = [
        'method',
        'endpoint',
        'request',
        'response',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    public function getSourceLabelAttribute(): string
    {
        $source = data_get($this->request, 'source');

        if (is_string($source) && trim($source) !== '') {
            return $source;
        }

        $endpoint = Str::of((string) $this->endpoint)->lower();

        if ($endpoint->contains(['onepay'])) {
            return 'OnePay';
        }

        if ($endpoint->contains(['webhook'])) {
            return 'Webhook';
        }

        if ($endpoint->contains(['registrations', 'payments', 'portal', 'orders', 'clients'])) {
            return 'Web đăng ký';
        }

        return 'API ngoài';
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match (strtoupper((string) $this->method)) {
            'POST' => 'bg-primary',
            'PUT' => 'bg-warning text-dark',
            'PATCH' => 'bg-info text-dark',
            'DELETE' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match (strtoupper((string) $this->status)) {
            'SUCCESS' => 'bg-success',
            'ERROR' => 'bg-warning text-dark',
            'EXCEPTION' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
