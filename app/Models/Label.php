<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Label
 *
 * @property int $id
 * @property string $name
 * @property float $width
 * @property float $height
 * @property string $unit
 * @property string|null $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|LabelDetail[] $label_details
 *
 * @package App\Models
 */
class Label extends BaseModel
{
	protected $table = 'labels';

    const STATUS_NEW        = 'NEW';
	const STATUS_ACTIVE     = 'ACTIVE';
	const STATUS_DELETED    = 'DELETED';

    const STATUES = [
        self::STATUS_NEW        => 'Mới',
        self::STATUS_ACTIVE     => 'Đang có',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW        => 'btn-secondary',
        self::STATUS_ACTIVE     => 'btn-primary',
    ];

    const ROTATE_NONE   = '0';
    const ROTATE_90     = '90';
    const ROTATE_180    = '180';
    const ROTATE_270    = '270';

    const ROTATES = [
        self::ROTATE_NONE   => '0',
        self::ROTATE_90     => '90',
        self::ROTATE_180    => '180',
        self::ROTATE_270    => '270',
    ];

	protected $casts = [
		'width'     => 'float',
		'height'    => 'float'
	];

	protected $fillable = [
		'event_id',
		'is_default',
		'name',
		'width',
		'height',
		'unit',
		'font',
		'font_link',
        'rotate',
		'type',
		'status',
        'created_by',
        'updated_by',
	];

	public function label_details()
	{
		return $this->hasMany(LabelDetail::class);
	}

    public function event()
	{
		return $this->belongsTo(Event::class);
	}

    public function user()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

    static public function getRotates()
    {
        return self::ROTATES;
    }

    static public function getStatues()
    {
        return self::STATUES;
    }

    public function getStatusText()
    {
        return self::STATUES[$this->status];
    }

    public function getStatusClass()
    {
        return self::STATUS_CLASS[$this->status];
    }
}
