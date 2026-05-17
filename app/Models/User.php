<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    const GENDER_MALE       = 1;
    const GENDER_FEMALE     = 2;

    const GENDERS = [
        self::GENDER_MALE       => 'Nam',
        self::GENDER_FEMALE     => 'Nữ',
    ];

    const GENDER_TITLE = [
        self::GENDER_MALE       => 'Mr.',
        self::GENDER_FEMALE     => 'Ms.',
    ];

    const TYPE_WEB          = "WEB";
    const TYPE_SCANNER      = "SCANNER";

    const TYPES = [
        self::TYPE_WEB          => 'Web',
        // self::TYPE_SCANNER      => 'Scanner',
    ];

    const TYPE_COLOR = [
        self::TYPE_WEB          => 'primary',
        self::TYPE_SCANNER      => 'secondary',
    ];

    const TYPE_ICONS = [
        self::TYPE_WEB          => '<i class="fa-solid fa-desktop"></i>',
        self::TYPE_SCANNER      => '<i class="fa-solid fa-qrcode"></i>',
    ];

    const STATUS_NEW            = 'NEW';
    const STATUS_ACTIVE         = 'ACTIVE';
    const STATUS_SUSPEND        = 'SUSPENDED';
    const STATUS_INACTIVE       = 'INACTIVE';
    const STATUS_HIDDEN         = 'HIDDEN';
    const STATUS_DELETED        = 'DELETED';

    const STATUES = [
        self::STATUS_ACTIVE     => 'Hoạt động',
        self::STATUS_INACTIVE   => 'Ngưng hoạt động',
        self::STATUS_SUSPEND    => 'Đình chỉ',
    ];

    const STATUS_CLASS = [
        self::STATUS_ACTIVE     => 'btn-primary',
        self::STATUS_INACTIVE   => 'btn-secondary',
        self::STATUS_SUSPEND    => 'btn-danger',
        self::STATUS_HIDDEN     => '',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'event_id',
        'package_id',
        'area_id',
        'login_code',
        'is_admin',
        'is_checkout',
        'gender',
        'username',
        'name',
        'email',
        'phone',
        'title',
        'position',
        'password',
        'expire_date',
        'verify_token',
        'session_id',
        'provider',
        'provider_id',
        'registered_at',
        'last_login_at',
        'type',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getGenders()
    {
        return [
            self::GENDER_MALE       => __('users.attributes.genders.male'),
            self::GENDER_FEMALE     => __('users.attributes.genders.female'),
        ];
    }

    public function getGender()
    {
        return self::GENDERS[$this->gender] ?? null;
    }

    public function getTitle()
    {
        return self::GENDER_TITLE[$this->gender] ?? null;
    }

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function getTypeText()
    {
        return self::TYPES[$this->type] ?? null;
    }

    public function getTypeColor()
    {
        return self::TYPE_COLOR[$this->type] ?? null;
    }

    public function getTypeIcons()
    {
        return self::TYPE_ICONS[$this->type] ?? null;
    }

    static public function getStatues()
    {
        return self::STATUES;
    }

    public function getStatusText()
    {
        return self::STATUES[$this->status] ?? null;
    }

    public function getStatusClass()
    {
        return self::STATUS_CLASS[$this->status] ?? null;
    }

    public function isNew()
    {
        return empty($this->id) ? true : false;
    }

    /**
     * Get the user's fullname titleized.
     */
    public function getFullnameAttribute(): string
    {
        return Str::title($this->name);
    }

    /**
     * Normalize checkout flag so scan flow never depends on raw DB string values.
     */
    public function getIsCheckoutAttribute($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Scope a query to only include users registered last week.
     */
    public function scopeLastWeek(Builder $query): Builder
    {
        return $query->whereBetween('registered_at', [carbon('1 week ago'), now()])
            ->latest();
    }

    /**
     * Scope a query to order users by latest registered.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('registered_at', 'desc');
    }

    /**
     * Scope a query to filter available author users.
     */
    public function scopeAuthors(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('roles.name', Role::ROLE_ADMIN)
                ->orWhere('roles.name', Role::ROLE_EDITOR);
        });
    }

    /**
     * Check if the user can be an author
     */
    public function canBeAuthor(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    /**
     * Check if the user has a role
     */
    public function hasRole(string $role): bool
    {
        // Split the roles by the "|" delimiter to handle multiple roles
        $roles = explode('|', $role);

        // Check if the user has any of the specified roles
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
        // return $this->roles->where('name', $role)->isNotEmpty();
    }

    /**
     * Check if the user has role admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }

    /**
     * Check if the user has role admin
     */
    public function isSysAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if the user has role editor
     */
    public function isEditor(): bool
    {
        return $this->hasRole(Role::ROLE_EDITOR);
    }

    public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}

	public function area()
	{
		return $this->belongsTo(EventArea::class);
	}

    /**
     * Return the user's roles
     */
    public function roles(): belongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function checkIfValidUser(?array $types = [])
    {
        if (count($types)) {
            if (!in_array($this->type, $types)) {
                return false;
            }
        }

        if ($this->isSysAdmin()) {
            return true;
        }

        if (!$this->isAdmin()) {
            if ($this->hasRole(Role::ROLE_USER)) {
                return true;
            }

            return false;
        }

        if (empty($this->package_id)) {
            return false;
        }

        if (in_array($this->status, [
            self::STATUS_NEW,
            self::STATUS_SUSPEND,
            self::STATUS_INACTIVE,
            self::STATUS_DELETED,
        ])) {
            return false;
        }

        if (!empty($this->expire_date)) {
            return $this->checkIfNotExpired();
        }

        return true;
    }

    public function checkIfNotExpired()
    {
        if (!empty($this->expire_date)) {
            if ((Helper::compareDateToToday($this->expire_date) == -1)) {
                return false;
            }

        }

        return true;
    }

    public static function getDateFields()
    {
        return [
            'last_login_at' => "Đăng nhập lần cuối",
            'registered_at' => "Thời gian đăng ký",
            'expire_date'   => "Ngày hết hạn",
            'created_at'    => "Thời gian tạo",
            'updated_at'    => "Thời gian cập nhật",
        ];
    }

    public function validateFeature(string $feature)
    {
        if ($this->package_id) {
            $exceptFeatures = config("info.packages.{$this->package->code}.excepts.features") ?? [];

            /* Chùa Minh Hiệp */
            if ($this->email == "cmh01@gmail.com") {
                $exceptFeatures = array_filter($exceptFeatures, function ($item) {
                    return !in_array($item, [
                        'landing_pages',
                        'emails',
                    ]);
                });

                $exceptFeatures = array_values($exceptFeatures);
            }

            /* Thành - MKT */
            if ($this->email == "thanh.nv@delfi.com.vn") {
                $exceptFeatures = array_filter($exceptFeatures, function ($item) {
                    return !in_array($item, [
                        'emails',
                    ]);
                });

                $exceptFeatures = array_values($exceptFeatures);
            }

            if (!empty($exceptFeatures) && count($exceptFeatures)) {
                if (in_array($feature, $exceptFeatures)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function validateSetting(string $settingName)
    {
        if ($this->package_id) {
            $exceptSettings = config("info.packages.{$this->package->code}.excepts.settings") ?? [];

            /* Chùa Minh Hiệp */
            if ($this->email == "cmh01@gmail.com") {
                $exceptSettings = array_filter($exceptSettings, function ($item) {
                    return !in_array($item, [
                        'OPEN_LANDING_PAGE',
                        'ENABLE_FORM',
                        'ENABLE_CAPTCHA',
                        'REGISTER_CHECKIN',
                        'REGISTER_SEND_EMAIL',
                    ]);
                });

                $exceptSettings = array_values($exceptSettings);
            }

            if (!empty($exceptSettings) && count($exceptSettings)) {
                if (in_array($settingName, $exceptSettings)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function authorizeSelfByEventId(int $eventId)
    {
        $event = Event::find($eventId);
        return self::authorizeSelfByEvent($event);
    }

    public function authorizeSelfByEvent(Event $event)
    {
        if ($event->company_id === $this->company_id) {
            if (!in_array($event->status, [
                    Event::STATUS_HIDDEN,
                    Event::STATUS_DELETED,
                ])) {
                return true; // The event belongs to the user's company
            }
        }

        return false;
    }

    public function authorizeObjectByEvent($object)
    {
        if (empty($object->event)) {
            return false;
        }

        if ($object->event->company_id === $this->company_id) {
            return true;
        }

        return false;
    }
}
