<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Card
 *
 * @property int $id
 * @property int $event_id
 * @property string $event_code
 * @property string|null $client_type
 * @property string $code
 * @property string|null $name
 * @property string|null $background
 * @property string|null $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|CardDetail[] $card_details
 *
 * @package App\Models
 */
class Card extends BaseModel
{
	protected $table = 'cards';

    const STATUS_NEW        = 'NEW';
	const STATUS_EDIT       = 'EDIT';
	const STATUS_INPROCESS  = 'INPROCESS';
	const STATUS_COMPLETED  = 'COMPLETED';
	const STATUS_DELETED    = 'DELETED';

    const STATUES = [
        self::STATUS_NEW        => 'Mới',
        self::STATUS_EDIT       => 'Thay đổi',
        self::STATUS_INPROCESS  => 'Đang chạy',
        self::STATUS_COMPLETED  => 'Hoàn tất',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW        => 'btn-secondary',
        self::STATUS_EDIT       => 'btn-primary',
        self::STATUS_INPROCESS  => 'btn-secondary',
        self::STATUS_COMPLETED  => 'btn-success',
    ];

    const STATUS_ICONS = [
        self::STATUS_EDIT       => '<i class="bx bx-notepad"></i>',
        self::STATUS_INPROCESS  => '<i class="bx bxs-analyse bx-spin"></i>',
        self::STATUS_COMPLETED  => '<i class="bx bx-check"></i>',
    ];

    const EXTENSION_PNG   = 'png';
    const EXTENSION_JPG   = 'jpg';
    const EXTENSION_JPEG  = 'jpeg';

    const EXTENSIONS = [
        self::EXTENSION_PNG     => '.png',
        self::EXTENSION_JPG     => '.jpg',
        self::EXTENSION_JPEG    => '.jpeg',
    ];

    const DEVICE_BOTH           = 'BOTH';
    const DEVICE_MOBILE         = 'MOBILE';
    const DEVICE_DESKTOP        = 'DESKTOP';

    const DEVICES = [
        self::DEVICE_BOTH       => 'Cả hai',
        self::DEVICE_MOBILE     => 'Điện thoại',
        self::DEVICE_DESKTOP    => 'Desktop',
    ];

	protected $casts = [
		'event_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'event_code',
		'client_type',
		'file_name_template',
		'code',
        // 'title',
		// 'name',
		'background',
        'extension',
        'scaled',
        'device',
		'type',
		'note',
		'status'
	];

    public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function card_details()
	{
		return $this->hasMany(CardDetail::class);
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

    static public function getExtensions()
    {
        return self::EXTENSIONS;
    }

    public function getExtensionsText()
    {
        return self::EXTENSIONS[$this->status];
    }

    public function backgroundUrl()
	{
		return $this->belongsTo(Media::class, 'background');
	}

    public function getMediaFields()
    {
        return [
            'background'    => [
                'label'     => 'Background',
                'object'    => $this->backgroundUrl,
            ],
        ];
    }
}
