<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Client
 *
 * @property int $id
 * @property int $event_id
 * @property string $qrcode
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property array|null $custom_fields
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 *
 * @package App\Models
 */
class Client extends BaseModel
{
    use HasFactory;

    /* STATUS */
    const STATUS_NEW            = 'NEW';
    const STATUS_ACTIVE         = 'ACTIVE';
    const STATUS_INACTIVE       = 'INACTIVE';
    const STATUS_DELETED        = 'DELETED';

    const STATUES = [
        self::STATUS_NEW        => 'New',
        self::STATUS_ACTIVE     => 'Active',
        self::STATUS_INACTIVE   => 'In-Active',
        self::STATUS_DELETED    => 'Deleted',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW        => 'btn-xs btn-secondary',
        self::STATUS_ACTIVE     => 'btn-xs btn-primary',
        self::STATUS_INACTIVE   => 'btn-xs btn-secondary',
        self::STATUS_DELETED    => 'btn-xs btn-danger',
    ];

    /* TYPES */

    const TYPE_NORMAL   = 'NORMAL';
    const TYPE_VIP      = 'VIP';

    /* REGISTER SOURCES */

    const REGISTER_ADMIN    = 'ADMIN';
    const REGISTER_IMPORT   = 'IMPORT';
    const REGISTER_LP       = 'LANDING_PAGE';
    const REGISTER_PC       = 'PC';
    const REGISTER_WEB      = 'WEB';
    const REGISTER_PDA      = 'PDA';
    const REGISTER_API      = 'API';

    const REGISTER_SOURCES = [
        self::REGISTER_ADMIN        => 'Admin tạo',
        self::REGISTER_IMPORT       => 'Nạp file/Đăng ký trước',
        self::REGISTER_LP           => 'Đăng ký Landing Page',
        self::REGISTER_WEB          => 'Thêm mới trên Web',
        self::REGISTER_API          => 'ApiService',
        // self::REGISTER_PC           => 'Thêm mới trên phần mềm PC',
        // self::REGISTER_PDA          => 'Thêm mới trên phần mềm PC',
    ];

	protected $table = 'clients';

	const MAIN_FIELDS = [
        'qrcode',
        'name',
        'email',
        'phone',
        'avatar',
        'event_id',
        'event_code'
    ];

	protected $casts = [
		'event_id'          => 'int',
		'custom_fields'     => 'json'
	];

	protected $fillable = [
		'event_id',
		'ref_id',
		'lp_id',
		'country_id',
		'event_code',
		'qrcode',
		'img_qrcode',
        'document_pdf',
        'card_link_mobile',
        'card_link_desktop',
		'name',
		'email',
		'avatar',
        'lang',
        'type',
        'register_source',
		'custom_fields',
		'status',
        'created_by',
		'updated_by',
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

    public function country()
	{
		return $this->belongsTo(Country::class);
	}

    public function emails()
    {
        return $this->hasMany(Email::class, 'qrcode', 'qrcode');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function registrationFiles()
    {
        return $this->hasMany(RegistrationFile::class);
    }

    public function user()
	{
        return $this->belongsTo(User::class, 'updated_by');
	}

    /* custom_fields type = type value */
    protected static function booted()
    {
        static::saving(function ($client) {
            if (isset($client->custom_fields['type'])) {
                $fields = $client->custom_fields;
                $fields['type'] = $client->type;
                $client->custom_fields = $fields;
            }
        });
    }

    /*
        $clients = Client::with('checkins')
            ->where('event_code', $eventCode)
            ->get();
    */
    public function checkins()
    {
        return $this->hasMany(Checkin::class, 'qrcode', 'qrcode')
                    ->whereColumn('checkins.qrcode', 'qrcode')
                    ->whereColumn('checkins.event_code', 'event_code');
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

    public function getRegisterSourceText()
    {
        return self::REGISTER_SOURCES[$this->register_source] ?? $this->register_source;
    }

    static public function getTypes()
    {
        return [
            self::TYPE_NORMAL => 'Thường',
            self::TYPE_VIP => 'VIP',
        ];
    }

    static public function getRegisterSources()
    {
        return self::REGISTER_SOURCES;
    }

    public function getMainFieldValue()
    {
        $name = $this->name;

        return [
            'qrcode'        => $this->qrcode,
            'name'          => $name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'avatar'        => $this->avatar,
            'img_qrcode'    => $this->img_qrcode,
            'type'          => $this->type,
        ];
    }

    public function getPhoneAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        if (!is_array($this->custom_fields ?? null)) {
            return null;
        }

        return $this->custom_fields['phone'] ?? null;
    }

    // public function getCustomFields()
    // {
    //     return $this->custom_fields;
    // }

    public function getFullFieldValue()
    {
        return array_merge(
            $this->getMainFieldValue(),
            is_array($this->custom_fields) ? $this->custom_fields : []
        );
    }

    public function findCheckin()
    {
        return Checkin::where('event_id', $this->event_id)
                    ->where('qrcode', $this->qrcode)
                    ->where('status', '!=', Checkin::STATUS_DELETED)
                    ->where('type', Checkin::TYPE_CHECKIN)
                    ->first();
    }

    public function findCheckout()
    {
        return Checkin::where('event_id', $this->event_id)
                    ->where('qrcode', $this->qrcode)
                    ->where('status', '!=', Checkin::STATUS_DELETED)
                    ->where('type', Checkin::TYPE_CHECKOUT)
                    ->first();
    }

    static public function getAvailableTypes(?int $eventId = 0)
    {
        $query = self::select('type');

        if ($eventId > 0) {
            $query->where('event_id', $eventId);
        }

        return $query->distinct()
            ->pluck('type')
            ->mapWithKeys(function ($item) {
                if ($item === null || $item === '') {
                    return ['empty' => 'Trống'];
                }
                return [$item => self::getTypes()[$item] ?? $item];
            })
            ->toArray();
    }

    static public function getAvailableRegisterSources()
    {
        return self::select('register_source')
            ->where('register_source', '!=', '')
            ->whereNotNull('register_source')
            ->distinct()
            ->pluck('register_source')
            ->mapWithKeys(function ($item) {
                return [$item => self::getRegisterSources()[$item] ?? $item];
            })
            ->toArray();
    }

    public function avatarUrl()
    {
        return $this->belongsTo(Media::class, 'avatar');
    }

    public function getImgQrcode(bool $byId = true)
    {
        if ($this->img_qrcode) {
            if ($byId) {
                return route('clients.view-qrcode-by-id', [
                    'id' => $this->id
                ]);
            }

            // return route('clients.view-qrcode', [
            //     'qrcode' => $this->qrcode
            // ]);
        }

        return null;
    }

    public function generateImgQrcode(bool $force = false)
    {
        if ($this->qrcode && $this->event) {
            if ($this->img_qrcode) {
                if ($force) {
                    return $this->event->generateImgQrcodeOnSetting(
                        $this->qrcode,
                        $this->phone,
                        $this->email,
                        $this->name,
                        $this->custom_fields ?? [],
                    );
                }
            }

            return $this->event->generateImgQrcodeOnSetting(
                $this->qrcode,
                $this->phone,
                $this->email,
                $this->name,
                $this->custom_fields ?? [],
            );
        }

        return null;
    }

    public static function getDateFields()
    {
        return [
            'created_at' => "Thời gian tạo",
            'updated_at' => "Thời gian cập nhật",
        ];
    }

    public function getMainFields()
    {
        return [
            'id'            => $this->id,
            'qrcode'        => $this->qrcode,
            'name'          => $this->name,
            'email'         => $this->email,
            'type'          => $this->type,
            'avatar'        => $this->avatar ? $this->avatarUrl->getUrl() : null,
            'img_qrcode'    => $this->img_qrcode ? route('clients.view-qrcode-by-id', [
                'id'        => $this->id
            ]) : null
        ];
    }

    public function getCustomFields(bool $html = false)
    {
        $customFields = $this->custom_fields ?? [];
        $customFieldTemplates = $this->event->getCustomFieldTemplates();
        if (is_string($customFields)) return [];

        foreach ($customFields as $field => $value) {
            $customFields[$field] = isset($customFieldTemplates[$field]) ? $this->getCustomFieldValue($customFieldTemplates[$field], $html) : null;
        }

        return $customFields;
        return $this->custom_fields;
    }

    public function getFullFields(bool $html = false)
    {
        return array_merge($this->getMainFields(), $this->getCustomFields($html));
    }

    public function getCustomFieldValues($html = false)
    {
        $customFields = $this->custom_fields;
        $newCustomFields = [];

        if (!$customFields) return [];

        foreach ($customFields as $key => $value) {
            if (isset($this->event->getCustomFieldTemplates()[$key])) {
                $newCustomFields[$key] = $this->getCustomFieldValue($this->event->getCustomFieldTemplates()[$key], $html);
                continue;
            }
            $newCustomFields[$key] = $this->custom_fields[$key];
        }
        return $newCustomFields;
    }

    public function getCustomFieldValue($attrField, $html = false)
    {
        $text = null;
        $fieldName = $attrField['name'];
        $fieldType = $attrField['type'];
        $customFields = $this->custom_fields;

        switch ($fieldType) {
            case CustomFieldTemplate::TYPE_CHECKBOX:
                $text = isset($customFields[$fieldName]) && $customFields[$fieldName] ? true : false;
                break;
            case CustomFieldTemplate::TYPE_SELECT:
                $item = $customFields[$fieldName] ?? null;
                $options = $attrField['options'];
                if (!empty($options) && is_array($options)) {
                    $item = $options[$item] ?? $item;
                }
                if ($item) {
                    if ($html) {
                        $text = "<div class='short-item'>{$item}</div>";
                    } else {
                        $text = $item;
                    }
                }
                break;
            case CustomFieldTemplate::TYPE_SELECT2:
                $item = $customFields[$fieldName] ?? null;
                $options = $attrField['options'];
                if (!empty($options) && is_array($options)) {
                    $item = $options[$item] ?? $item;
                }
                if ($item) {
                    if ($html) {
                        $text = "<div class='short-item'>{$item}</div>";
                    } else {
                        $text = $item;
                    }
                }
                break;
            case CustomFieldTemplate::TYPE_RADIO:
                $item = $customFields[$fieldName] ?? null;
                $options = $attrField['options'];
                if (!empty($options) && is_array($options)) {
                    $item = $options[$item] ?? $item;
                }
                if ($item) {
                    if ($html) {
                        $text = "<div class='short-item'>{$item}</div>";
                    } else {
                        $text = $item;
                    }
                }
                break;
            case CustomFieldTemplate::TYPE_MULTICHOICE:
                $listText = $customFields[$fieldName] ?? [];
                if (is_array($listText)) {
                    $options = $attrField['options'];
                    $validItems = [];
                    foreach ($listText as $item) {
                        $value = $item;
                        if (!empty($options) && is_array($options)) {
                            $value = !empty($item) ? ($options[$item] ?? $item) : $item;
                        }
                        if ($value) {
                            $validItems[] = $value;
                        }
                    }
                    if ($html) {
                        $text = '';
                        $lastIndex = count($validItems) - 1;
                        foreach ($validItems as $i => $val) {
                            $text .= $val;
                            if ($i !== $lastIndex) {
                                $text .= '<br>';
                            }
                        }
                    } else {
                        $text .= implode(", \n", $validItems);
                    }
                }

                break;
            case CustomFieldTemplate::TYPE_LINK:
                $text = $customFields[$fieldName] ?? null;
                $text = '<a href="'.$text.'" target="_blank">Link</a>';
                break;
            default:
                $text = $customFields[$fieldName] ?? null;
                break;
        }

        return $text;
    }
}
