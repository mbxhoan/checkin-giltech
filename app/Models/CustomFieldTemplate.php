<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Predis\Command\Argument\Server\To;

/**
 * Class CustomFieldTemplate
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $description
 * @property int $order
 * @property array|null $checkin
 * @property array|null $landing_page
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 *
 * @package App\Models
 */
class CustomFieldTemplate extends BaseModel
{
	protected $table = 'custom_field_templates';

    const TYPE_CODE             = 'CODE';
    const TYPE_TEXT             = 'TEXT';
    const TYPE_NUMBER           = 'NUMBER';
    const TYPE_TEL              = 'TEL';
    const TYPE_EMAIL            = 'EMAIL';
    const TYPE_COLOR            = 'COLOR';
    const TYPE_SELECT           = 'SELECT';
    const TYPE_MULTICHOICE      = 'MULTICHOICE';
    const TYPE_RADIO            = 'RADIO';
    const TYPE_CHECKBOX         = 'CHECKBOX';
    const TYPE_SWITCH           = 'SWITCH';
    const TYPE_AVATAR           = 'AVATAR';
    const TYPE_IMAGE            = 'IMAGE';
    const TYPE_FILE             = 'FILE';
    const TYPE_HIDDEN           = 'HIDDEN';
    const TYPE_TEXT_FIX         = 'TEXT_FIX';
    const TYPE_LINK             = 'LINK';
    const TYPE_DATE             = 'DATE';
    const TYPE_SELECT2          = 'SELECT2';

    const TYPES_TEXT = [
        self::TYPE_TEXT         => 'text',
        self::TYPE_CODE         => 'mã',
        self::TYPE_NUMBER       => 'số',
        self::TYPE_TEL          => 'sđt',
        self::TYPE_EMAIL        => 'email',
        self::TYPE_CHECKBOX     => 'checkbox',
        self::TYPE_COLOR        => 'màu sắc',
        self::TYPE_SELECT       => 'chọn',
        self::TYPE_MULTICHOICE  => 'multichoice',
        self::TYPE_RADIO        => 'radio',
        self::TYPE_SWITCH       => 'switch',
        self::TYPE_AVATAR       => 'avatar',
        self::TYPE_IMAGE        => 'link ảnh',
        self::TYPE_FILE         => 'tệp',
        self::TYPE_HIDDEN       => 'hidden',
        self::TYPE_TEXT_FIX     => 'text cố định',
        self::TYPE_LINK         => 'đường dẫn',
        self::TYPE_DATE         => 'ngày tháng',
        self::TYPE_SELECT2      => 'chọn (select2)',
    ];

    const TYPES = [
        self::TYPE_TEXT         => 'text',
        self::TYPE_NUMBER       => 'number',
        self::TYPE_TEL          => 'text',
        self::TYPE_EMAIL        => 'text',
        self::TYPE_CODE         => 'text',
        self::TYPE_CHECKBOX     => 'checkbox',
        self::TYPE_COLOR        => 'color',
        self::TYPE_SELECT       => 'select',
        self::TYPE_MULTICHOICE  => 'multichoice',
        self::TYPE_RADIO        => 'radio',
        self::TYPE_SWITCH       => 'switch',
        self::TYPE_AVATAR       => 'avatar',
        self::TYPE_IMAGE        => 'image',
        self::TYPE_FILE         => 'file',
        self::TYPE_HIDDEN       => 'hidden',
        self::TYPE_TEXT_FIX     => 'text',
        self::TYPE_LINK         => 'text',
        self::TYPE_DATE         => 'date',
        self::TYPE_SELECT2      => 'select2',
    ];

    const TYPE_USE_OPTIONS  = [
        self::TYPE_SELECT,
        self::TYPE_SELECT2,
        self::TYPE_MULTICHOICE,
        self::TYPE_RADIO
    ];

    // const ALIGN_LEFT        = 'left';
    // const ALIGN_RIGHT       = 'right';
    // const ALIGN_CENTER      = 'center';

    // const ALIGNS = [
    //     self::ALIGN_LEFT    => 'Trái',
    //     self::ALIGN_CENTER  => 'Giữa',
    //     self::ALIGN_RIGHT   => 'Phải',
    // ];

	protected $casts = [
		'event_id' => 'int',
		'order' => 'int',
		'checkins' => 'json',
		'landing_page' => 'json'
	];

	protected $fillable = [
		'event_id',
        'is_default',
        'is_show',
        'is_lp',
        'is_checkin_mobile',
        'is_checkin_desktop',
        'show_prefix',
        'required',
        'unique',
		'name',
		'description',
		'placeholder',
		'icon',
		'order',
        'type',
        'accepts',
        'options',
		'checkins',
		'landing_page'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

    public function getDefaultCustomFieldTemplate()
    {
        return [
            // Mã định danh khách mời
            'qrcode'    => [
                'desc'                  => "Qrcode",
                'type'                  => self::TYPE_CODE,
                'required'              => true,
                'unique'                => true,
                'is_lp'                 => false,
                'is_checkin_desktop'    => false,
                'is_checkin_mobile'     => false,
            ],
            // Họ, tên khách mời
            'name'      => [
                'desc'                  => "Họ, tên",
                'type'                  => self::TYPE_TEXT,
                'required'              => true,
                'unique'                => false,
                'is_lp'                 => true,
                'is_checkin_desktop'    => false,
                'is_checkin_mobile'     => false,
            ],
            // Email khách mời
            'email'     => [
                'desc'                  => "Email",
                'type'                  => self::TYPE_EMAIL,
                'required'              => true,
                'unique'                => true,
                'is_lp'                 => false,
                'is_checkin_desktop'    => false,
                'is_checkin_mobile'     => false,
            ],
        ];
    }

    static public function getTypes()
    {
        return self::TYPES_TEXT;
    }

    public function getTypeGroup(string $type)
    {
        return self::TYPES[$type] ?? null;
    }

    public function getTypeText(string $type)
    {
        return self::TYPES_TEXT[$type] ?? null;
    }

    public function getOptionsAsArray(?array $options = [])
    {
        if (!count($options)) {
            $options = !empty($this->options) ? json_decode($this->options, true) : [];
        }

        foreach ($options as $option) {
            // lang_trans("{$event->code}.fields.{$fieldName}", "lp", $fieldAttr['desc'] ?? $fieldName)
            $convertedOptions[$option['key']] = $option['value'];
        }

        return $convertedOptions ?? [];
    }
}
