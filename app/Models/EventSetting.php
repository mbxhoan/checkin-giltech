<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventSetting
 *
 * @property int $id
 * @property int $event_id
 * @property int $is_checked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 *
 * @package App\Models
 */
class EventSetting extends BaseModel
{
	protected $table = 'event_settings';

    const GROUP_LP          = 'LANDING_PAGE';
    const GROUP_PAYMENT     = 'PAYMENT';
    const GROUP_QRCODE      = 'QRCODE';
    const GROUP_EMAIL       = 'EMAIL';
    const GROUP_BOX         = 'BOX';
    const GROUP_DESKTOP     = 'DESKTOP';
    const GROUP_TABLET      = 'TABLET';
    const GROUP_MOBILE      = 'MOBILE';

    const GROUPS = [
        self::GROUP_LP          => 'Landing page',
        self::GROUP_PAYMENT     => 'Payment',
        self::GROUP_QRCODE      => 'Qrcode <i class="fa-solid fa-qrcode"></i>',
        self::GROUP_DESKTOP     => 'Checkin <i class="fa-solid fa-desktop"></i>',
        self::GROUP_MOBILE      => 'Checkin <i class="fa-solid fa-mobile-screen"></i>'
    ];

    const INPUT_TYPE_TEXT       = 'TEXT';
    const INPUT_TYPE_NUM        = 'NUMBER';
    const INPUT_TYPE_SWITCH     = 'SWITCH';
    const INPUT_TYPE_CHECKBOX   = 'CHECKBOX';
    const INPUT_TYPE_COLOR      = 'COLOR';
    const INPUT_TYPE_SELECT     = "SELECT";

    const INPUT_TYPES = [
        self::INPUT_TYPE_TEXT       => 'text',
        self::INPUT_TYPE_NUM        => 'number',
        self::INPUT_TYPE_SWITCH     => 'switch',
        self::INPUT_TYPE_CHECKBOX   => 'checkbox',
        self::INPUT_TYPE_COLOR      => 'color',
        self::INPUT_TYPE_SELECT     => 'select',
    ];

	protected $casts = [
		'event_id'      => 'int',
        'value'         => 'string',
	];

	protected $fillable = [
		'parent_id',
		'event_id',
		'name',
		'description',
		'value',
        'options',
		'group',
		'status',
		'input_type',
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

    static public function getEventSettingGroup()
    {
        return self::GROUPS;
    }

    static public function getEventSettingType()
    {
        return self::INPUT_TYPES;
    }

    static public function getOptionsQrcodeCorrection()
    {
        return [
            'L' => 'Thấp (7% dữ liệu)',
            // 'N' => 'Trung Bình (15% dữ liệu)',
            'Q' => 'Khá (25% dữ liệu)',
            'H' => 'Cao (30% dữ liệu)',
        ];
    }

    static public function getOptionsQrcodeOutput()
    {
        return [
            'png' => '.png',
            // 'jpg' => '.jpg',
        ];
    }

    static public function getOptionsQrcodeLogoWidth()
    {
        return [
            '0.1' => '10%',
            '0.2' => '20%',
            '0.3' => '30%',
            '0.4' => '40%',
            '0.5' => '50%',
        ];
    }

    /* static public function getDefaultJsonText()
    {
        return [
            "desktop" => [
                "success" => [
                    "field" => "success",
                    "name" => null,
                    "description" => null,
                    "attributes" => [
                        "show" => "true",
                        "font_size" => "20",
                        "font" => "Roboto",
                        "color" => "#00ff04",
                        "bg_color" => "#ffffff",
                        "align" => "center",
                        "valign" => "top",
                        "width" => "0",
                        "height" => "0",
                        "pos_x" => "0",
                        "pos_y" => "250"
                    ]
                ],
                "error" => [
                    "field" => "error",
                    "name" => null,
                    "description" => null,
                    "attributes" => [
                        "show" => "true",
                        "font_size" => "20",
                        "font" => "Roboto",
                        "color" => "#ff0000",
                        "bg_color" => "#ffffff",
                        "align" => "center",
                        "valign" => "top",
                        "width" => "0",
                        "height" => "0",
                        "pos_x" => "0",
                        "pos_y" => "100"
                    ]
                ]
            ],
            "pda" => [
                "success" => [
                    "field" => "success",
                    "name" => null,
                    "description" => "Checkin thành công",
                    "attributes" => [
                        "show" => "true",
                        "font_size" => "20",
                        "font" => "Roboto",
                        "color" => "#00ff04",
                        "bg_color" => "#ffffff",
                        "align" => "center",
                        "valign" => "top",
                        "width" => "0",
                        "height" => "0",
                        "pos_x" => "0",
                        "pos_y" => "250"
                    ]
                ],
                "error" => [
                    "field" => "error",
                    "name" => null,
                    "description" => "Mã QR không đúng",
                    "attributes" => [
                        "show" => "true",
                        "font_size" => "20",
                        "font" => "Roboto",
                        "color" => "#ff0000",
                        "bg_color" => "#ffffff",
                        "align" => "center",
                        "valign" => "top",
                        "width" => "0",
                        "height" => "0",
                        "pos_x" => "0",
                        "pos_y" => "100"
                    ]
                ]
            ],
        ];

        return '{
            "image":[],
            "desktop": {
                "success": {
                    "field": "success",
                    "name":null,
                    "description":null,
                    "attributes": {
                        "show":"true",
                        "font_size":"20",
                        "font":"Roboto",
                        "color":"#00ff04",
                        "bg_color":"#ffffff",
                        "align":"center",
                        "valign":"top",
                        "width":"0",
                        "height":"0",
                        "pos_x":"0",
                        "pos_y":"250"
                    }
                },
                "error": {
                    "field":"error",
                    "name":null,
                    "description":null,
                    "attributes": {
                        "show":"true",
                        "font_size":"20",
                        "font":"Roboto",
                        "color":"#ff0000",
                        "bg_color":"#ffffff",
                        "align":"center",
                        "valign":"top",
                        "width":"0",
                        "height":"0",
                        "pos_x":"0",
                        "pos_y":"100"
                    }
                }
            },
            "pda": {
                "success": {
                    "field": "success",
                    "name":null,
                    "description":null,
                    "attributes": {
                        "show":"true",
                        "font_size":"20",
                        "font":"Roboto",
                        "color":"#00ff04",
                        "bg_color":"#ffffff",
                        "align":"center",
                        "valign":"top",
                        "width":"0",
                        "height":"0",
                        "pos_x":"0",
                        "pos_y":"250"
                    }
                },
                "error": {
                    "field":"error",
                    "name":null,
                    "description":null,
                    "attributes": {
                        "show":"true",
                        "font_size":"20",
                        "font":"Roboto",
                        "color":"#ff0000",
                        "bg_color":"#ffffff",
                        "align":"center",
                        "valign":"top",
                        "width":"0",
                        "height":"0",
                        "pos_x":"0",
                        "pos_y":"100"
                    }
                }
            }
        }';
    } */
}
