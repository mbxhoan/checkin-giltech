<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Campaign
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 * @property Collection|CampaignDetail[] $campaign_details
 *
 * @package App\Models
 */
class Campaign extends BaseModel
{
	protected $table = 'campaigns';

	const STATUS_NEW        = 'NEW';
	const STATUS_ACTIVE     = 'ACTIVE';
	const STATUS_SENDING    = 'SENDING';
	const STATUS_COMPLETED  = 'COMPLETED';
	const STATUS_DELETED    = 'DELETED';

    const STATUES = [
        self::STATUS_NEW        => 'Mới',
        self::STATUS_ACTIVE     => 'Đã kích hoạt',
        self::STATUS_SENDING    => 'Đang gửi',
        self::STATUS_COMPLETED  => 'Hoàn tất',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW        => 'btn-secondary',
        self::STATUS_ACTIVE     => 'btn-primary',
        self::STATUS_SENDING    => 'btn-warning',
        self::STATUS_COMPLETED  => 'btn-success',
    ];

    const STATUS_ICONS = [
        self::STATUS_ACTIVE     => '<i class="bx bx-notepad"></i>',
        self::STATUS_SENDING    => '<i class="bx bxs-analyse bx-spin"></i>',
        self::STATUS_COMPLETED  => '<i class="bx bx-check"></i>',
    ];

	protected $casts = [
		'event_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'template_id',
		'is_online',
		'name',
		'type',
		'subject',
		'from_email',
		'from_name',
        'total_emails',
		'status',
        'cc',
        'bcc',
        'message_stream',
        'limitation_per_time',
        'hold_time',
        'fixed_attachments',
        'created_by',
        'updated_by',
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function campaign_details()
	{
		return $this->hasMany(CampaignDetail::class);
	}

	public function emails()
	{
		return $this->hasMany(Email::class);
	}

	public function getEmails($statuses)
	{
        if (is_array($statuses)) {
            return $this->hasMany(Email::class)->whereIn('status', $statuses);
        }

        return $this->hasMany(Email::class)->where('status', $statuses);
	}

    public function user()
	{
        return $this->belongsTo(User::class, 'updated_by');
	}

    public function landingPages()
    {
        return $this->belongsToMany(LandingPage::class, 'landing_page_campaigns')
            ->withPivot('lang')
            ->withTimestamps();
    }

    public function landingPageCampaigns()
    {
        return $this->hasMany(LandingPageCampaign::class);
    }

	// static public function getTypes()
    // {
    //     return self::TYPES;
    // }

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

    public function getStatusIcon()
    {
        return self::STATUS_ICONS[$this->status];
    }
}
