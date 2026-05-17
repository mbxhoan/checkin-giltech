<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Checkin
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string $qrcode
 * @property string|null $client_name
 * @property Carbon $scan_time
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 * @property User $user
 *
 * @package App\Models
 */
class Checkin extends BaseModel
{
	protected $table = 'checkins';

    const NO_DATA_NAME      = "no_name";

    const STATUS_CHECKIN 	= 'CHECKIN';
	const STATUS_NEW 		= 'NEW';
	const STATUS_DELETED    = 'DELETED';

    const TYPE_CHECKIN 		= 'CHECKIN';
    const TYPE_CHECKOUT 	= 'CHECKOUT';

    const TYPES = [
        self::TYPE_CHECKIN  => 'Checkin',
        self::TYPE_CHECKOUT => 'Checkout',
    ];

	protected $casts = [
		'event_id' => 'int',
		'user_id' => 'int'
	];

	protected $dates = [
		'scan_time'
	];

	protected $fillable = [
		'event_id',
		'event_code',
		'user_id',
		'qrcode',
		// 'device_name',
		'scan_time',
		'custom_fields',
		'type',
		'note',
		'status',
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function client()
    {
        return $this->belongsTo(Client::class, 'qrcode', 'qrcode')
                ->whereColumn('clients.event_code', 'event_code');
    }

    static public function getTypes()
    {
        return self::TYPES;
    }

    public static function getDateFields()
    {
        return [
            'scan_time' => "Thời gian quét",
        ];
    }
}
