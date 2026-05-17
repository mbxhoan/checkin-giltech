<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LuckyDraw
 *
 * @property int $id
 * @property int|null $event_id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event|null $event
 * @property Collection|LuckyDrawReward[] $lucky_draw_rewards
 *
 * @package App\Models
 */
class LuckyDraw extends BaseModel
{
	protected $table = 'lucky_draws';

    const TYPE_RAFFLE       = 'RAFFLE';
	const TYPE_WHEEL        = 'WHEEL';

    const TYPES = [
		self::TYPE_RAFFLE   => 'Raffle',
        self::TYPE_WHEEL    => 'Lucky Wheel',
    ];

	protected $casts = [
		'event_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'name',
		'background_url_mobile',
		'background_url_desktop',
		'type',
		'status',
        'created_by',
        'updated_by',
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function clients()
	{
		return $this->hasMany(LuckyDrawClient::class);
	}

	public function rewards()
	{
		return $this->hasMany(LuckyDrawReward::class);
	}

    public function created_by()
	{
        return $this->belongsTo(Admin::class, 'created_by');
	}

    public function updated_by()
	{
        return $this->belongsTo(Admin::class, 'updated_by');
	}

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function bgMobileUrl()
	{
		return $this->belongsTo(Media::class, 'background_url_mobile');
	}

    public function bgDesktopUrl()
	{
		return $this->belongsTo(Media::class, 'background_url_desktop');
	}

    public function getMediaFields()
    {
        return [
            'background_url_desktop'    => [
                'label'     => 'Màn hình máy tính <i class="fa-solid fa-desktop"></i>',
                'object'    => $this->bgDesktopUrl,
            ],
            'background_url_mobile'    => [
                'label'     => 'Màn hình điện thoại <i class="fa-solid fa-mobile-screen"></i>',
                'object'    => $this->bgMobileUrl,
            ],
        ];
    }
}
