<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Company
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Event[] $events
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Company extends BaseModel
{
	protected $table = 'companys';

    const PREFIX = "CMP";

    const STATUS_NEW    = 'NEW';

    const STATUES = [
        self::STATUS_NEW        => 'Mới',
        self::STATUS_ACTIVE     => 'Đang hoạt động',
        self::STATUS_INACTIVE   => 'Ngưng hoạt động',
    ];

    const TYPE_1            = 1;
    const TYPE_2            = 2;
    const TYPE_3            = 3;

    const TYPES = [
        self::TYPE_1        => "Hội họp/Event",
        self::TYPE_2        => "Triển lãm/Hội chợ",
        self::TYPE_3        => "Sự kiện âm nhạc/Lễ hội, sự kiện nội bộ (*)",
    ];

	protected $fillable = [
		'is_default',
		'license',
		'name',
		'code',
        'limited_events',
        'limited_users',
        'limited_campaigns',
        'limited_emails',
        'limited_clients',
		'status',
		'languages',
		'settings',
		'devices',
		'templates',
		'senders',
		'type',
		'created_by',
		'updated_by',
	];

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function getTypeText()
    {
        return self::TYPES[$this->type] ?? null;
    }

    static public function getStatues()
    {
        $statues = array_merge(parent::STATUES, self::STATUES);
        return $statues;
    }

    public function getStatusText()
    {
        $statues = array_merge(parent::STATUES, self::STATUES);
        return $statues[$this->status];
    }

    public function user()
	{
        return $this->belongsTo(User::class, 'updated_by');
	}

	public function events()
	{
		return $this->hasMany(Event::class);
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}

	public function audios()
	{
		return $this->hasMany(Audio::class);
	}

    /**
     * Check if the company has a language
     */
    public function hasLanguage(int $languageId): bool
    {
        return $this->languages && in_array($languageId, json_decode($this->languages, true));
    }

    public function getLanguages()
    {
        $languageIds = array_values($this->languages ? json_decode($this->languages, true) : []);
        return Language::whereIn('id', $languageIds)
            ->where('status', '!=', Language::STATUS_DELETED)
            ->get();
    }

    public function getUpdatedAtAttribute($date)
    {
        return date("Y-m-d H:i:s", strtotime($date));
    }

    public function getCreatedAtAttribute($date)
    {
        return date("Y-m-d", strtotime($date));
    }

	public static function getCompanyDefault()
    {
        return 1;
    }

    public static function generateCode($length = 12)
    {
        return Helper::randomCode($length);
    }
}
