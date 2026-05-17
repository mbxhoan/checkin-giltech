<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailLog
 * 
 * @property int $id
 * @property int $event_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $content
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Event $event
 *
 * @package App\Models
 */
class EmailLog extends BaseModel
{
	protected $table = 'email_logs';

	protected $casts = [
		'event_id' => 'int',
		'portal_user_id' => 'int',
		'registration_id' => 'int',
		'order_id' => 'int',
		'sent_at' => 'datetime',
		'metadata' => 'array',
	];

	protected $fillable = [
		'event_id',
		'portal_user_id',
		'registration_id',
		'order_id',
		'type',
		'subject',
		'name',
		'email',
		'content',
		'status',
		'provider_message_id',
		'sent_at',
		'error_message',
		'metadata',
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

	public function order()
	{
		return $this->belongsTo(Order::class);
	}
}
