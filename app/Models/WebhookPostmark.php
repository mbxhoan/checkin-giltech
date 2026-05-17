<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateTimeZone;

/**
 * Class WebhookPostmark
 *
 * @property int $id
 * @property string $server_id
 * @property string $message_id
 * @property string $message_stream
 * @property string $email
 * @property string|null $tag
 * @property string|null $details
 * @property Carbon $record_time
 * @property string $status
 * @property array|null $metadata
 * @property array|null $response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class WebhookPostmark extends Model
{
	protected $table = 'webhook_postmarks';

    const STATUS_DELIVERY           = 'Delivery';
    const STATUS_BOUNCE             = 'Bounce';
    const STATUS_SPAM               = 'SpamComplaint';
    const STATUS_OPEN               = 'Open';
    const STATUS_CLICK              = 'Click';
    const STATUS_SUPSCRIPTIONCHANGE = 'SubscriptionChange';

    const STATUS_CLASS = [
        self::STATUS_DELIVERY           => 'primary',
        self::STATUS_BOUNCE             => 'danger',
        self::STATUS_SPAM               => 'warning',
        self::STATUS_OPEN               => 'success',
        self::STATUS_CLICK              => 'success',
        self::STATUS_SUPSCRIPTIONCHANGE => 'default',
    ];

	protected $casts = [
		'record_time' => 'datetime',
		'metadata' => 'json',
		'response' => 'json'
	];

	protected $fillable = [
		'email_id',
		'server_id',
		'ip_address',
		'message_id',
		'message_stream',
		'email',
		'tag',
		'details',
		'record_time',
		'status',
		'metadata',
		'response'
	];

    /* public function email()
    {
        return $this->belongsTo(Email::class, 'email_id', 'id');
    } */

    public function recipient()
    {
        return $this->belongsTo(Email::class, 'message_id', 'message_id');
    }

    public function getStatusClass()
    {
        return self::STATUS_CLASS[$this->status];
    }

    public function convertToLocalTime()
    {
        $webhookTime = $this->record_time;
        // return \Carbon\Carbon::parse($webhookTime)->addHours(7)->toDateTimeString();

        $utcTimezone = new DateTimeZone('UTC');
        $localTimezone = new DateTimeZone(env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'));

        $date = new DateTime($webhookTime, $utcTimezone);
        $date->setTimezone($localTimezone);
        $webhookTime = $date->format('Y-m-d H:i:s');
        return $webhookTime;
    }
}
