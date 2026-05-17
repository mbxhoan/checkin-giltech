<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Class Event
 *
 * @property int $id
 * @property int $company_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string|null $place
 * @property int|null $run_date
 * @property Carbon|null $pda_reg
 * @property string|null $pc_bg
 * @property string|null $pda_bg
 * @property string|null $luckydraw_bg
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property array|null $client_custom_fields
 * @property string|null $note
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Company $company
 * @property Collection|Checkin[] $checkins
 * @property Collection|Client[] $clients
 *
 * @package App\Models
 */
class Event extends BaseModel
{
	protected $table = 'events';

    const STATUS_NEW    = 'NEW';
    const STATUS_HIDDEN = 'HIDDEN';
    const STATUS_DONE   = 'DONE';

    const STATUES = [
        self::STATUS_NEW        => 'Mới',
        self::STATUS_ACTIVE     => 'Đang triển khai',
        self::STATUS_INACTIVE   => 'Tạm dừng',
        self::STATUS_DONE       => 'Hoàn tất',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW    => 'btn-secondary',
        self::STATUS_HIDDEN => '',
        self::STATUS_DONE   => 'btn-success',
    ];

    const TYPE_FIELD_MAIN = 'MAIN';
    const TYPE_FIELD_CUSTOM = 'CUSTOM';

    const ALIGN_LEFT        = 'left';
    const ALIGN_RIGHT       = 'right';
    const ALIGN_CENTER      = 'center';

    const ALIGNS = [
        self::ALIGN_LEFT    => 'Trái',
        self::ALIGN_CENTER  => 'Giữa',
        self::ALIGN_RIGHT   => 'Phải',
    ];

    const ALIGN_IN_BOOTSTRAP = [
        self::ALIGN_LEFT     => 'start',
        self::ALIGN_CENTER   => 'center',
        self::ALIGN_RIGHT    => 'end',
    ];

    const WIDTH_5           = '5';
    const WIDTH_10          = '10';
    const WIDTH_15          = '15';
    const WIDTH_25          = '25';
    const WIDTH_35          = '35';
    const WIDTH_45          = '45';
    const WIDTH_50          = '50';
    const WIDTH_65          = '65';
    const WIDTH_75          = '75';
    const WIDTH_85          = '85';
    const WIDTH_95          = '95';
    const WIDTH_100         = '100';

    const WIDTHS = [
        self::WIDTH_5       => '5%',
        self::WIDTH_10      => '10%',
        self::WIDTH_15      => '15%',
        self::WIDTH_25      => '25%',
        self::WIDTH_35      => '35%',
        self::WIDTH_45      => '45%',
        self::WIDTH_50      => '50%',
        self::WIDTH_65      => '65%',
        self::WIDTH_75      => '75%',
        self::WIDTH_85      => '85%',
        self::WIDTH_95      => '95%',
        self::WIDTH_100     => '100%',
    ];

    const TEXT          = 'text';
    const TEXTHIDDEN    = 'texthidden';
    // const CHECKBOX      = 'checkbox';
    const MULTICHOICE   = 'multichoice';
    const RADIO         = 'radio';
    const SELECT        = 'select';
    const NUMBER        = 'number';
    const EMAIL         = 'email';
    const TEL           = 'tel';
    const TICKET        = 'ticket';

    const LANDINGPAGE_FORM_TYPE = [
        self::TEXT          => 'Textbox',
        self::TEXTHIDDEN    => 'Textbox Hidden',
        // self::CHECKBOX      => 'Checbox',
        self::MULTICHOICE   => 'Multichoice',
        self::RADIO         => 'Radio',
        self::SELECT        => 'Select',
        self::NUMBER        => 'Number',
        self::EMAIL         => 'Email',
        self::TEL           => 'Telephone',
        self::TICKET        => 'Ticket',
    ];

	protected $casts = [
		'company_id' => 'int',
		'from_date' => 'datetime',
		'to_date' => 'datetime',
		'languages' => 'json',
		'more_images' => 'json',
		'is_default' => 'bool',
		'import_error_log' => 'json',
		'created_by' => 'int',
		'updated_by' => 'int',
		'province_id' => 'int'
	];

	protected $dates = [
		'from_date',
		'to_date'
	];

	protected $fillable = [
		'company_id',
        'ref_id',
		'code',
		'name',
		'description',
		'place',
		'logo',
		'favicon',
		'from_date',
		'to_date',
		'features',
		'languages',
		'more_images',
		'main_bg_desktop',
		'main_bg_mobile',
		'main_bglandingpage_desktop',
		'main_bglandingpage_mobile',
        'sound_success',
        'sound_fail',
		'custom_checkin_messages',
		'contact_person',
		'contact_phone',
		'contact_email',
		'is_default',
		'note',
		'import_error_log',
		'status',
		'created_by',
		'updated_by',
		'province_id'
	];

    public function user()
	{
        return $this->belongsTo(User::class, 'updated_by');
	}

    public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function campaigns()
	{
		return $this->hasMany(Campaign::class);
	}

	public function checkins()
	{
		return $this->hasMany(Checkin::class);
	}

	public function clients()
	{
		return $this->hasMany(Client::class);
	}

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_code', 'code');
    }

    public function custom_field_templates()
	{
		return $this->hasMany(CustomFieldTemplate::class);
	}

	public function event_files()
	{
		return $this->hasMany(EventFile::class);
	}

	public function event_settings()
	{
		return $this->hasMany(EventSetting::class);
	}

    public function areas()
    {
        return $this->hasMany(EventArea::class);
    }

	public function export_datas()
	{
		return $this->hasMany(ExportData::class);
	}

	public function landing_pages()
	{
		return $this->hasMany(LandingPage::class);
	}

	public function cards()
	{
		return $this->hasMany(Card::class);
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}

	public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function languages()
	{
		return $this->belongsToMany(Language::class, 'event_languages')
					->withPivot('id')
					->withTimestamps();
	}

	public function exportDatas()
	{
		return $this->hasMany(ExportData::class);
	}

    public function labels()
	{
		return $this->hasMany(Label::class)
            ->orderBy('is_default', 'DESC');
	}

    public static function getDateFields()
    {
        return [
            'run_date'      => "Thời gian diễn ra",
            'created_at'    => "Thời gian tạo",
            'updated_at'    => "Thời gian cập nhật",
        ];
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

    public function getStatusClass()
    {
        $statues = array_merge(parent::STATUS_CLASS, self::STATUS_CLASS);
        return $statues[$this->status];
    }

    static public function getAligns()
    {
        return self::ALIGNS;
    }

    public function getAlignInBootstrap()
    {
        return self::ALIGN_IN_BOOTSTRAP[$this->align] ?? null;
    }

    static public function getLandingPageFormTypes()
    {
        return self::LANDINGPAGE_FORM_TYPE;
    }

    static public function getWidths()
    {
        return self::WIDTHS;
    }

    public function logoUrl()
    {
        return $this->belongsTo(Media::class, 'logo');
    }

    public function faviconUrl()
    {
        return $this->belongsTo(Media::class, 'favicon');
    }

    public function mainBgDesktop()
    {
        return $this->belongsTo(Media::class, 'main_bg_desktop');
    }

    public function mainBgMobile()
    {
        return $this->belongsTo(Media::class, 'main_bg_mobile');
    }

    public function getCustomFieldTemplates($includeDefault = false, $includeLp = null, $includeCheckin = null, array $filters = [])
    {
        $templates = $this->custom_field_templates;

        if (!$includeDefault) {
            $templates = $templates->where('is_default', false);
        }

        if (isset($includeLp)) {
            if ($includeLp) {
                $templates = $templates->where('is_lp', true);
            } else {
                $templates = $templates->where('is_lp', false);
            }
        }

        $templates = $templates->sortBy('order');

        if (count($templates)) {
            foreach ($filters as $key => $value) {
                $templates = $templates->where($key, $value);
            }
        }

        $templates = $templates->mapWithKeys(function ($template) {
                return [
                    $template->name => [
                        'is_default'    => $template->is_default,
                        'name'          => $template->name,
                        'required'      => $template->required,
                        'unique'        => $template->unique,
                        'desc'          => $template->description,
                        'type'          => $template->type,
                        'options'       => $template->getOptionsAsArray(),
                        'accepts'       => !empty($template->accepts) ? json_decode($template->accepts, true) : [],
                    ]
                ];
            })
            ->toArray();

        return $templates;
    }

    public function getEventSettings(?string $group = null)
    {
        $settings = $this->event_settings->whereIn('status', [
            EventSetting::STATUS_ACTIVE
        ]);

        if ($group) {
            $settings = $settings->where('group', $group);
        }

        return $settings;
    }

    public function getEventSetting(string $name, ?string $group = null)
    {
        if ($name == "OPEN_LANDING_PAGE") {
            return (object)[
                'value' => 1,
            ];
        }

        $setting = $this->event_settings->whereIn('status', [
            EventSetting::STATUS_ACTIVE
        ]);;

        if ($group) {
            $setting = $setting->where('group', $group);
        }

        return $setting->firstWhere('name', $name);
    }

    public function getFonts()
    {
        return [
            'roboto'            => 'Roboto',
            'public-sans'       => 'Public Sans',
            'montserrat'        => 'Montserrat' ,
            'source-code-pro'   => 'Source Code Pro',
            'anton'             => 'Anton',
            'merriweather'      => 'Merriweather',
            'balow'             => 'Balow',
            'great_vibes'       => 'Great Vibes',
            'geologica'         => 'Geologica',
        ];
    }

    public function generateQrcodeOnSetting($eventCode, $phone = null, $email = null, $name = null, $customFields = [])
    {
        $eventSettings = $this->getEventSettings(EventSetting::GROUP_QRCODE);

        foreach ($eventSettings as $setting) {
            switch ($setting->name) {
                case config('event-settings.QRCODE.generate_custom_qrcode.name'):
                    $value = $setting->value;

                    if ($value) {
                        $fields = [
                            "name"      => $name,
                            "phone"     => $phone,
                            "email"     => $email,
                        ];

                        $fields = array_merge($fields, $customFields);
                        $qrcode = Helper::generateQrcodeByTemplate($value, $fields);
                    }

                    break;
                default:
                    $qrcode = Helper::generateSimpleQrcode($eventCode);
                    break;
            }
        }

        return $qrcode ?? Helper::generateSimpleQrcode($eventCode);
    }

    public function generateImgQrcodeOnSetting($qrcode, $phone = null, $email = null, $name = null, $customFields = [])
    {
        $eventSettings = $this->getEventSettings(EventSetting::GROUP_QRCODE);
        $customFields = is_array($customFields) ? $customFields : [];
        $config = [
            'white_border'  => true,
            'is_barcode'    => false,
            'logo_width'    => .3
        ];

        try {
            foreach ($eventSettings as $setting) {
                switch ($setting->name) {
                    case config('event-settings.QRCODE.qrcode_attach_logo.name'):
                        if ($setting->value) {
                            if ($this->logo && is_numeric($this->logo)) {
                                $config['logo_path'] = $this->logoUrl->getPath();
                            } else {
                                $config['logo_path'] = $this->logo;
                            }
                        }
                        break;
                    case config('event-settings.QRCODE.qrcode_logo_width.name'):
                        if (!empty($config['logo_path'])) {
                            $config['logo_width'] = $setting->value;
                        }
                        break;
                    case config('event-settings.QRCODE.qrcode_attach_text.name'):
                        $config['with_text'] = $setting->value ? true : false;
                        break;
                    case config('event-settings.QRCODE.qrcode_color.name'):
                        $config['qrcode_color'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_bg_color.name'):
                        $config['qrcode_bg_color'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_correction.name'):
                        $config['qrcode_correction'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_output.name'):
                        $config['output'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.custom_file_name.name'):
                        $value = $setting->value;

                        if ($value) {
                            $fields = [
                                "name"      => $name,
                                "phone"     => $phone,
                                "email"     => $email,
                            ];

                            $fields = array_merge($fields, $customFields);
                            $fileName = Helper::generateQrcodeByTemplate($value, $fields);
                        }

                        $config['file_name'] = $fileName ?? Helper::removeSpecialCharacters($qrcode);
                        break;
                    default:
                        break;
                }
            }

            if ($this->code === 'pinaco') {
                $pinacoToken = trim((string) ($customFields['token'] ?? ''));

                if ($pinacoToken !== '') {
                    $config['with_text'] = true;
                    $config['display_text'] = $pinacoToken;

                    $fileNameParts = array_filter([
                        Helper::removeSpaceOnStr($pinacoToken, true, true, true),
                        !empty($name) ? Helper::removeSpaceOnStr(trim((string) $name), true, true, true) : null,
                    ], static fn ($value) => $value !== null && $value !== '');

                    if (!empty($fileNameParts)) {
                        $config['file_name'] = Helper::removeSpecialCharacters(implode('_', $fileNameParts));
                    }
                }
            }

            $imgQrcode = Helper::generateImgQrcode(
                $qrcode,
                $this->code,
                $config ?? [],
            );
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return null;
        }

        return $imgQrcode ?? null;
    }

    public function getScanLink()
    {
        if (config('app.env') == "local") {
            return "http://".config('app.scan_domain').":8000/scan/{$this->id}";
        }

        return "https://".config('app.scan_domain')."/scan/{$this->id}";
    }

    public static function generateUniqueEventCode(string $eventName, int $limit = 20): string
    {
       $baseCode = Str::slug($eventName);

        // Limit the base code to $limit characters
        $baseCode = Str::limit($baseCode, $limit, '');

        $code = $baseCode;
        $suffix = 1;

        // Check for uniqueness in the `events` table
        while (Event::where('code', $code)->exists()) {
            // Append suffix (e.g., tech-event-1)
            $code = Str::limit($baseCode, $limit - (strlen((string) $suffix) + 1), '') . '-' . $suffix;
            $suffix++;
        }

        return $code;
    }

    public function hasFeature(string $featureKey): bool
    {
        return $this->features && in_array($featureKey, array_values(json_decode($this->features, true)));
    }

    public function getProgressOnGoing()
    {
        if ($this->features) {
            $features = json_decode($this->features, true);
            $p = 0;
            foreach ($features as $feature) {
                switch ($feature) {
                    case 'e-5':
                        $hasBackground = collect([
                            $this->main_bg_desktop,
                            $this->main_bg_mobile,
                            $this->main_bglandingpage_desktop,
                            $this->main_bglandingpage_mobile
                        ])->filter()->isNotEmpty();
                        if ($hasBackground) $p += 1;
                        break;
                    case 'e-6':
                        $hasCheckin = $this->custom_field_templates->contains(function ($template) {
                            return !empty($template->checkins);
                        });
                        if ($hasCheckin) $p += 1;
                        break;
                    case 'e-7':
                        $hasLandingPages = $this->landing_pages()
                            ->where('status', '!=', LandingPage::STATUS_DELETED)
                            ->exists();
                        if ($hasLandingPages) $p += 1;
                        break;
                    case 'e-9':
                        $hasCards = $this->cards()
                            ->where('status', '!=', Card::STATUS_DELETED)
                            ->exists();
                        if ($hasCards) $p += 1;
                        break;
                    case 'e-9':
                        $hasLabels = $this->labels()
                            ->where('status', '!=', Label::STATUS_DELETED)
                            ->exists();
                        if ($hasLabels) $p += 1;
                        break;
                    default:
                        $p += 1;
                }
            }

            // $this->progress = number_format($p/count($features), 2);
            $this->progress = $p;
            $this->total = count($features);
        } else {
            $this->progress = 3;
            $this->total = 10;
        }

        return $this;
    }
}
