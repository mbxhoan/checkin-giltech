<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class LandingPage
 *
 * @property int $id
 * @property int $event_id
 * @property string $slug
 * @property array|null $customs
 * @property array|null $orders
 * @property string $align
 * @property int|null $banner_id
 * @property int|null $header_id
 * @property int|null $footer_id
 * @property string|null $bg_desktop
 * @property string|null $bg_tablet
 * @property string|null $bg_mobile
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string|null $contact_address
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Medium|null $medium
 * @property Event $event
 *
 * @package App\Models
 */
class LandingPage extends BaseModel
{
    use SoftDeletes;
	protected $table = 'landing_pages';

    const FORM_WIDTH_25     = '3';
    const FORM_WIDTH_35     = '4';
    const FORM_WIDTH_45     = '5';
    const FORM_WIDTH_50     = '6';
    const FORM_WIDTH_75     = '9';
    const FORM_WIDTH_100    = '12';

    const FORM_WIDTHS = [
        self::FORM_WIDTH_25     => '25%',
        self::FORM_WIDTH_35     => '35%',
        self::FORM_WIDTH_45     => '45%',
        self::FORM_WIDTH_50     => '50%',
        self::FORM_WIDTH_75     => '75%',
        self::FORM_WIDTH_100    => '100%',
    ];

    // const ALIGN_LEFT        = 'left';
    // const ALIGN_CENTER      = 'center';
    // const ALIGN_RIGHT       = 'right';

    // const ALIGNS = [
    //     self::ALIGN_LEFT     => 'Trái',
    //     self::ALIGN_CENTER   => 'Giữa',
    //     self::ALIGN_RIGHT    => 'Phải',
    // ];

	protected $casts = [
		'event_id' => 'int',
		'customs' => 'json',
		'orders' => 'json',
		'banner_id' => 'int',
		'header_id' => 'int',
		'footer_id' => 'int'
	];

	protected $fillable = [
		'template_id',
		'event_id',
		'show_language_selection',
		'slug',
		'tracking',
		'customs',
		'orders',
		'align',
		'form_width',
		'languages',
		'banner_id',
		'header_id',
		'footer_id',
		'bg_desktop_id',
		'bg_tablet_id',
		'bg_mobile_id',
		'contact_name',
		'contact_phone',
		'contact_email',
		'contact_address',
        'created_by',
        'updated_by',
		'status',
        'deleted_at'
	];

    public function user()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function bg_desktop()
	{
		return $this->belongsTo(Media::class, 'bg_desktop_id');
	}

	public function bg_tablet()
	{
		return $this->belongsTo(Media::class, 'bg_tablet_id');
	}

	public function bg_mobile()
	{
		return $this->belongsTo(Media::class, 'bg_mobile_id');
	}

	public function header()
	{
		return $this->belongsTo(Media::class, 'header_id');
	}

	public function banner()
	{
		return $this->belongsTo(Media::class, 'banner_id');
	}

	public function footer()
	{
		return $this->belongsTo(Media::class, 'footer_id');
	}

    public static function getDateFields()
    {
        return [
            'created_at' => "Thời gian tạo",
            'updated_at' => "Thời gian cập nhật",
        ];
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

    public function getTranslate(string $keyword, ?string $languageCode = null)
    {
        if (!$languageCode) {
            return null;
        }

        return LanguageDefine::where('keyword', $keyword)
            ->whereHas('language', function ($query) use ($languageCode) {
                $query->where('code', $languageCode);
            })
            ->where('event_id', $this->event_id)
            ->where('status', '!=', LanguageDefine::STATUS_DELETED)
            ->first();
    }

    public function getLanguageByCode(?string $languageCode = null)
    {
        if (!$languageCode) {
            return null;
        }

        return Language::where('code', $languageCode)
            ->first();
    }

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'landing_page_campaigns')
            ->withPivot('lang')
            ->withTimestamps();
    }

    public function landingPageCampaigns()
    {
        return $this->hasMany(LandingPageCampaign::class);
    }

    public function landingPageCards()
    {
        return $this->hasMany(LandingPageCard::class);
    }

    public function accesses()
    {
        return $this->hasMany(PageAccessLog::class, 'lp_id');
    }

    public function syncCampaignsByLang(array $campaignIdsByLang)
    {
        // Remove all existing relations for this landing page
        $this->landingPageCampaigns()->delete();

        // Insert new relations
        foreach ($campaignIdsByLang as $lang => $campaignId) {
            if ($campaignId) {
                if (in_array($lang, $this->getLanguages()->pluck('code')->toArray())) {
                    $this->landingPageCampaigns()->create([
                        'campaign_id' => $campaignId,
                        'lang'        => $lang,
                    ]);
                }
            }
        }
    }

    public function syncCardsByLang(array $cardIdsByLang)
    {
        // Remove all existing relations for this landing page
        $this->landingPageCards()->delete();

        // Insert new relations
        foreach ($cardIdsByLang as $lang => $cardId) {
            if ($cardId) {
                if (in_array($lang, $this->getLanguages()->pluck('code')->toArray())) {
                    $this->landingPageCards()->create([
                        'card_id'       => $cardId,
                        'lang'          => $lang,
                    ]);
                }
            }
        }
    }

    static public function getFormWidths()
    {
        return self::FORM_WIDTHS;
    }

    public function checkIfLandingPageIsValid()
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE
        ]);

        return $this->event->getEventSetting("OPEN_LANDING_PAGE", null)->value ?? null;
    }

    public function getCustomByKey(?string $keyword = null, ?string $field = null)
    {
        $customs = $this->customs;

        if ($keyword) {
            if ($field) {
                switch ($field) {
                    case 'font':
                        $default = "roboto";
                        break;
                    case 'font_size':
                        $default = "22";
                        break;
                    case 'color':
                        $default = "#000000";
                        break;
                    case 'bg_color':
                        $default = "#ffffff";
                        break;
                    // Add more mappings if needed
                    default:
                        $default = "";
                        break;
                }

                return $customs[$keyword][$field] ?? $default;
            }

            return $customs[$keyword] ?? [];
        }

        return $customs;
    }

    public function generateCssFromCustoms(?string $keyword = null, string $suffix = "text"): string
    {
        $customs = $this->customs ?? [];
        if (!count($customs)) return "<stye></stye>";

        if ($keyword) {
            $tmpCustom = [];
            $tmpCustom[$keyword] = $customs[$keyword] ?? [];
            $customs = $tmpCustom;
        }

        $css = '<style>';

        foreach ($customs as $selector => $styles) {
            if (isset($suffix)) {
                $css .= "#$selector-{$suffix} {";
            } else {
                $css .= ".$selector {";
            }

            foreach ($styles as $key => $value) {
                switch ($key) {
                    case 'font':
                        $css .= "font-family: '{$value}', sans-serif;";
                        break;
                    case 'font_size':
                        $css .= "font-size: {$value}px;";
                        break;
                    case 'color':
                        $css .= "color: {$value};";
                        break;
                    case 'bg_color':
                        $css .= "background-color: {$value};";
                        break;
                    // Add more mappings if needed
                    default:
                        $css .= "{$key}: {$value};";
                        break;
                }
            }

            $css .= '}';
        }

        $css .= '</style>';
        return $css;
    }

    public function getCustomsTemplate()
    {
        return [
            'custom_head1',
            'custom_bottom1',
        ];
    }

    public function getMediaFields()
    {
        return [
            'banner_id'     => [
                'label'     => 'Banner',
                'object'    => $this->banner,
            ],
            // 'header_id'     => [],
            // 'header_id'     => [
            //     'label'     => 'Header',
            //     'object'    => $this->header,
            // ],
            // 'footer_id'     => [],
            // 'footer_id'     => [
            //     'label'     => 'Footer',
            //     'object'    => $this->footer,
            // ],
            'bg_desktop_id' => [
                'label'     => 'Background PC <i class="fa-solid fa-desktop"></i>',
                'object'    => $this->bg_desktop,
            ],
            // 'bg_tablet_id'  => [],
            // 'bg_tablet_id'  => [
            //     'label'     => 'Background Tablet',
            //     'object'    => $this->bg_tablet,
            // ],
            'bg_mobile_id'  => [
                'label'     => 'Background Mobile <i class="fa-solid fa-mobile-screen"></i>',
                'object'    => $this->bg_mobile,
            ],
        ];
    }

    public static function getTemplates()
    {
        return [
            '1' => [
                'name' => 'single-form',
                'text' => 'Form đơn',
                'path' => 'assets/images/landing_pages/templates/1.png',
            ],
            '2' => [
                'name' => 'side-form',
                'text' => 'Form lớn (trái - phải)',
                'path' => 'assets/images/landing_pages/templates/2.png',
            ],
            '3' => [
                'name' => 'mini-form',
                'text' => 'Form thu nhỏ',
                'path' => 'assets/images/landing_pages/templates/3.png',
            ],
        ];
    }

    public function getRegisterUrl()
    {
        $domain = env("REGISER_DOMAIN");
        $slug = $this->slug;

        if ($domain) {
            if (env("APP_ENV") === 'local') {
                $domain = "http://{$domain}";
            } else {
                $domain = "https://{$domain}";
            }

            return "{$domain}/{$slug}";
        }

        return route("landing_pages.register", $slug);
    }
}
