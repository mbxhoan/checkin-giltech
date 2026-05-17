<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Intervention\Image\Facades\Image;

/**
 * Class CardDetail
 *
 * @property int $id
 * @property int $card_id
 * @property string $card_code
 * @property string|null $type
 * @property string|null $img_path
 * @property int|null $pos_x
 * @property int|null $pos_y
 * @property int|null $font_size
 * @property int|null $width
 * @property int|null $height
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Card $card
 *
 * @package App\Models
 */
class CardDetail extends BaseModel
{
	protected $table = 'card_details';

    /* STATUS */

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DELETED = 'DELETED';

    const STATUES = [
        self::STATUS_ACTIVE => 'Hiển thị',
        self::STATUS_DELETED => 'Ẩn',
    ];

    /* HORIZONTAL ALIGN */

    const H_ALIGN_LEFT     = 'left';
    const H_ALIGN_CENTER   = 'center';
    const H_ALIGN_RIGHT    = 'right';

    const H_ALIGNS = [
        self::H_ALIGN_LEFT     => 'Trái',
        self::H_ALIGN_CENTER   => 'Giữa',
        self::H_ALIGN_RIGHT    => 'Phải',
    ];

    /* VERTICAL ALIGN */

    const V_ALIGN_TOP       = 'top';
    const V_ALIGN_center    = 'center';
    const V_ALIGN_BOTTOM    = 'bottom';

    const V_ALIGNS = [
        self::V_ALIGN_TOP       => 'Top',
        self::V_ALIGN_center    => 'Middle',
        self::V_ALIGN_BOTTOM    => 'Bottom',
    ];

    /* TYPE */

    const TYPE_NONE     = '';
    const TYPE_FIELD    = 'FIELD';
    const TYPE_QRCODE   = 'QRCODE';
    const TYPE_IMG      = 'IMG';
    const TYPE_TEXT     = 'TEXT';

    const TYPES = [
        self::TYPE_FIELD    => 'Trường thông tin',
        // self::TYPE_QRCODE   => 'Qrcode',
        self::TYPE_IMG      => 'Ảnh',
        // self::TYPE_TEXT     => 'Text cố định',
    ];

	protected $casts = [
		'card_id'   => 'int',
		// 'pos_x'     => 'int',
		// 'pos_y'     => 'int',
		// 'font_size' => 'int',
		// 'width'     => 'int',
		// 'height'    => 'int'
	];

	protected $fillable = [
		'card_id',
		'card_code',
		'type',
		'field',
		'text',
		'text_wrap',
		'img_path',
		'pos_x',
		'pos_y',
		'font_size',
		'font',
		'width',
		'height',
		'bold',
		'italic',
		'color',
        'v_align',
        'h_align',
        'rotate',
		'status'
	];

	public function card()
	{
		return $this->belongsTo(Card::class);
	}

    /* STATUS */

    static public function getStatues()
    {
        return self::STATUES;
    }

    /* HORIZONTAL ALIGN */

    static public function getHAligns()
    {
        return self::H_ALIGNS;
    }

    public function getHAlignsText()
    {
        return self::H_ALIGNS[$this->h_align];
    }

    /* VERTICAL ALIGN */

    static public function getVAligns()
    {
        return self::V_ALIGNS;
    }

    public function getVAlignsText()
    {
        return self::V_ALIGNS[$this->v_align];
    }

    /* TYPE */

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function getTypesText()
    {
        return self::TYPES[$this->type];
    }

    public function getCssAttributes()
    {
         if (!empty($this->card->background)) {
        $path = $this->card->backgroundUrl->getPath();

        // Only proceed if file exists and is readable
        if (file_exists($path) && is_readable($path)) {
            try {
                $image = Image::make($path);
                $bgWidth = $image->width();
                $bgHeight = $image->height();
            } catch (\Exception $e) {
                // Optionally log the error for debugging
                \Log::warning("Unable to read image: {$path}. Error: " . $e->getMessage());
            }
        }
    }

        return [
            'font_size' => $this->font_size,
            'font'      => $this->font,
            'color'     => $this->color,
            'h_align'   => $this->h_align,
            'pos_x'     => $this->pos_x,
            'pos_y'     => $this->pos_y,
            'width'     => $this->width,
            'height'    => $this->height,
            'bg_width'  => !empty($this->card->background) ? ((file_exists($path) && is_readable($path)) ? (Image::make($this->card->backgroundUrl->getPath()))->width() : null) : null,
            'bg_height' => !empty($this->card->background) ? ((file_exists($path) && is_readable($path)) ? (Image::make($this->card->backgroundUrl->getPath()))->height() : null) : null,
        ];
    }

    public function generateCssFromAttributes(array $attributes, string $name, ?string $key = null): string
    {
        if (!count($attributes)) return "";
        $css = '<style>';
        $css .= "#{$name} {";

        if ($this->type == self::TYPE_FIELD) {
            // unset($attributes['width']);
            unset($attributes['height']);
            $unit = "%";
            $fontUnit = "px";
        } else {
            unset($attributes['font_size']);
            unset($attributes['font']);
            unset($attributes['color']);
            $unit = "px";
            $fontUnit = "px";
        }

        foreach ($this->getFieldAttributes() as $field => $attr) {
            if (!isset($attributes[$field])) continue;

            switch ($field) {
                case 'bold':
                    if ($attributes[$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'italic':
                    if ($attributes[$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'font':
                    $attributes[$field] = $this->getFont($attributes[$field])['name'] ?? null;
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$field] ?? 'roboto',
                    ]);
                    break;
                case 'font_size':
                    $fontSize = (float)($attributes[$field] ?? 50);
                    $css .= __("styles.{$field}", [
                        'value' => $fontSize,
                        'unit'  => "em",
                    ]);
                    break;

                    $fontSize = ($fontSize/100)*$attributes['bg_height'];
                    $css .= __("styles.{$field}", [
                        'value' => $fontSize,
                        'unit'  => $fontUnit,
                    ]);
                    break;
                case 'color':
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$field] ?? "#000000",
                    ]);
                    break;
                case 'bg_color':
                    $showBg = $attributes['bg'] ?? false;

                    if ($showBg) {
                        $css .= __("styles.{$field}", [
                            'value' => $attributes[$field] ?? "#ffffff",
                        ]);
                        $css .= __("styles.padding", [
                            'top'       => "2",
                            'right'     => "10",
                            'bottom'    => "2",
                            'left'      => "10",
                            'unit'      => "px",
                        ]);
                        $css .= __("styles.border_radius", [
                            'value'     => 10,
                            'unit'      => "px",
                        ]);
                    }
                    break;
                case 'width':
                    $css .= __("styles.{$field}", [
                        'value' => ($attributes[$field] ?? "50").$unit,
                    ]);
                    break;
                case 'height':
                    $css .= __("styles.{$field}", [
                        'value' => ($attributes[$field] ?? "50").$unit,
                    ]);
                    break;
                case 'pos_x':
                    $css .= __("styles.left", [
                        'value'     => ($attributes[$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'pos_y':
                    $css .= __("styles.top", [
                        'value'     => ($attributes[$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'h_align':
                    if ($attributes[$field] == self::H_ALIGN_CENTER) {
                        $css .= __("styles.text_align", [
                            'value' => "center",
                        ]);
                        $css .= "transform: translate(-50%, -50%);";
                        break;
                    }

                    $css .= __("styles.text_align", [
                        'value' => $attributes[$field] ?? 'left',
                    ]);
                    break;
                default:
                    break;
            }
        }

        $css .= '}';
        $css .= $this->generateFontFaceCss($this->font);
        $css .= '</style>';
        return $css;
    }

    function generateFontFaceCss(string $font): string
    {
        $font = $this->getFont($font);
        if (!count($font)) return "";
        $fontName = $font['name']; // e.g. "Montserrat-Regular"
        $fontDir = $font['path'];                      // e.g. "montserrat"
        $fontWeight = $font['weight'];                      // e.g. "montserrat"
        $fontStyle = $font['style'];                      // e.g. "montserrat"

        return <<<CSS
        @font-face {
            font-family: '{$fontName}';
            src: url('/assets/fonts/{$fontDir}') format('truetype');
            font-weight: '{$fontWeight}';
            font-style: '{$fontStyle}';
        }
        CSS;
    }

    public static function getFonts()
    {
        return [
            'roboto'            => [
                'name'          => 'roboto',
                'text'          => 'Roboto',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'Roboto/Roboto-Regular.ttf',
            ],
            'roboto-bold'       => [
                'name'          => 'roboto',
                'text'          => 'Roboto - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'Roboto/Roboto-Bold.ttf',
            ],
            'roboto-italic'     => [
                'name'          => 'roboto',
                'text'          => 'Roboto - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'italic',
                'path'          => 'Roboto/Roboto-Italic.ttf',
            ],
            'montserrat'        => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'montserrat/Montserrat-Regular.ttf',
            ],
            'montserrat-bold'   => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'montserrat/Montserrat-Bold.ttf',
            ],
            'montserrat-italic' => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'italic',
                'path'          => 'montserrat/Montserrat-Italic.ttf',
            ],
            // 'MTD-Portrait-Script-Bounce' => [
            //     'name'          => 'MTD-Portrait-Script-Bounce',
            //     'text'          => 'MTD-Portrait-Script-Bounce - In nghiêng',
            //     'weight'        => 'normal',
            //     'style'         => 'normal',
            //     'path'          => 'MTD-Portrait-Script-Bounce.ttf',
            // ],
            'HelveticaNeueLTStd-LtCn' => [
                'name'          => 'HelveticaNeueLTStd-LtCn',
                'text'          => 'HelveticaNeueLTStd-LtCn',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'HelveticaNeueLTStd-LtCn.ttf',
            ],
            'gilroy-bold'        => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - In đậm',
                'weight'        => '700',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-Bold.ttf',
            ],
            'gilroy-semibold'   => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Đậm vừa',
                'weight'        => '600',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-SemiBold.ttf',
            ],
            'gilroy-xbold'   => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Rất đậm',
                'weight'        => '800',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-XBold.ttf',
            ],
            'gilroy-light' => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Mảnh',
                'weight'        => '300',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-Light.ttf',
            ],
            'arsenalsc-bold' => [
                'name'          => 'arsenalsc',
                'text'          => 'arsenalsc - Đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'arsenalsc/ArsenalSC-Bold.ttf',
            ],
             'UnileverShilling'   => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShilling',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShilling.ttf',
            ],
            'UnileverShillingBold'   => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingBold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingBold.ttf',
            ],
            'UnileverShillingItalic' => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingItalic',
                'weight'        => 'italic',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingItalic.ttf',
            ],
            'UnileverShillingMedium' => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingMedium',
                'weight'        => 'medium',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingMedium.ttf',
            ],
            'arsenalsc-bold' => [
                'name'          => 'arsenalsc',
                'text'          => 'arsenalsc - Đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'arsenalsc/ArsenalSC-Bold.ttf',
            ],
            // 'LynkCoTypeVn-Bold' => [
            //     'name'          => 'LynkCoTypeVN',
            //     'text'          => 'LynkCoTypeVn - Bold',
            //     'weight'        => 'bold',
            //     'style'         => 'normal',
            //     'path'          => 'LynkCoTypeVN/LynkCoTypeVN-Bold.ttf',
            // ],
            // 'LynkCoTypeVn-Regular' => [
            //     'name'          => 'LynkCoTypeVN',
            //     'text'          => 'LynkCoTypeVn - Regular',
            //     'weight'        => 'regular',
            //     'style'         => 'normal',
            //     'path'          => 'LynkCoTypeVN/LynkCoTypeVN-Regular.ttf',
            // ],
            // 'LynkCoTypeVn-Medium' => [
            //     'name'          => 'LynkCoTypeVN',
            //     'text'          => 'LynkCoTypeVn - Medium',
            //     'weight'        => 'medium',
            //     'style'         => 'normal',
            //     'path'          => 'LynkCoTypeVN/LynkCoTypeVN-Medium.ttf',
            // ],
            // 'LynkCoTypeVn-Light' => [
            //     'name'          => 'LynkCoTypeVN',
            //     'text'          => 'LynkCoTypeVn - Light',
            //     'weight'        => 'light',
            //     'style'         => 'normal',
            //     'path'          => 'LynkCoTypeVN/LynkCoTypeVN-Light.ttf',
            // ],
            'BITTER-BOLD' => [
                'name'          => 'BITTER-BOLD',
                'text'          => 'BITTER-BOLD',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'BITTER-BOLD/BITTER-BOLD.TTF',
            ],
            'lato-medium' => [
                'name'          => 'lato',
                'text'          => 'lato-medium',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'lato/lato-medium.ttf',
            ],
            'Brandon-reg' => [
                'name'          => 'Brandon',
                'text'          => 'Brandon - Regular',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'Brandon/Brandon_reg.ttf',
            ],
            'Brandon-med' => [
                'name'          => 'Brandon',
                'text'          => 'Brandon - Medium',
                'weight'        => 'medium',
                'style'         => 'normal',
                'path'          => 'Brandon/Brandon_med_it.ttf',
            ],
            'Brandon-bld' => [
                'name'          => 'Brandon',
                'text'          => 'Brandon - Bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'Brandon/Brandon_bld.ttf',
            ],
            'UTM Helve' => [
                'name'          => 'UTM Helve',
                'text'          => 'UTM Helve',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'UTM-Helve/UTM Helve.ttf',
            ],
            'UTM-Helve-Bold' => [
                'name'          => 'UTM-Helve',
                'text'          => 'UTM-Helve - Bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'UTM-Helve/UTM Helve Bold.ttf',
            ],
            'UTM-Helve-Italic' => [
                'name'          => 'UTM-Helve',
                'text'          => 'UTM-Helve - Italic',
                'weight'        => 'italic',
                'style'         => 'normal',
                'path'          => 'UTM-Helve/UTM Helve_Italic.ttf',
            ],
            'montserrat-extrabold'   => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - Extra Bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'montserrat/Montserrat-ExtraBold.ttf',
            ],
            'montserrat-extrabold-italic' => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - Extra Bold Italic',
                'weight'        => 'bold',
                'style'         => 'italic',
                'path'          => 'montserrat/Montserrat-ExtraBoldItalic.ttf',
            ],
            // 'NotoSans-Bold' => [
            //     'name'          => 'NotoSans',
            //     'text'          => 'Noto Sans - In đậm',
            //     'weight'        => 'bold',
            //     'style'         => 'normal',
            //     'path'          => 'NotoSans/NotoSansBold.ttf',
            // ],
            // 'NotoSansSC-Italic' => [
            //     'name'          => 'NotoSans',
            //     'text'          => 'Noto Sans - In nghiêng',
            //     'weight'        => 'normal',
            //     'style'         => 'italic',
            //     'path'          => 'NotoSans/NotoSansItalic.ttf',
            // ],
            'NotoSansSC' => [
                'name'          => 'NotoSans',
                'text'          => 'Noto Sans - Trung',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'NotoSans/NotoSansSC.ttf',
            ],
            'NotoSansSC-Bold' => [
                'name'          => 'NotoSans',
                'text'          => 'Noto Sans - Trung - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'NotoSans/NotoSansSC-Bold.ttf',
            ],
            'NotoSansJP' => [
                'name'          => 'NotoSans',
                'text'          => 'Noto Sans - Nhật',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'NotoSans/NotoSansJP.ttf',
            ],
            'NotoSansKR' => [
                'name'          => 'NotoSans',
                'text'          => 'Noto Sans - Hàn',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'NotoSans/NotoSansKR.ttf',
            ],
            'CabinCondensedSemiBold' => [
                'name'          => 'Cabin',
                'text'          => 'CabinCondensedSemiBold',
                'weight'        => '600',
                'style'         => 'normal',
                'path'          => 'Cabin/Cabin-Condensed-SemiBold.ttf',
            ],
            'Cabin' => [
                'name'          => 'Cabin',
                'text'          => 'Cabin',
                'weight'        => '400',
                'style'         => 'normal',
                'path'          => 'Cabin/Cabin.ttf',
            ],
            // 'times-new-roman' => [
            //     'name'          => 'TimeNewRoman',
            //     'text'          => 'Times New Roman',
            //     'weight'        => 'regular',
            //     'style'         => 'normal',
            //     'path'          => 'TimeNewRoman/times-new-roman.ttf',
            // ],
            // 'times-new-roman-bold' => [
            //     'name'          => 'TimeNewRoman',
            //     'text'          => 'Times New Roman Bold',
            //     'weight'        => 'bold',
            //     'style'         => 'normal',
            //     'path'          => 'TimeNewRoman/times-new-roman-bold.ttf',
            // ],
            // 'times-new-roman-italic' => [
            //     'name'          => 'TimeNewRoman',
            //     'text'          => 'Times New Roman Italic',
            //     'weight'        => 'regular',
            //     'style'         => 'italic',
            //     'path'          => 'TimeNewRoman/times-new-roman-italic.ttf',
            // ],
            'SVN-Times-New-Roman' => [
                'name'          => 'TimeNewRoman',
                'text'          => 'SVN Times New Roman',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'TimeNewRoman/SVN-Times-New-Roman.ttf',
            ],
            'SVN-Times-New-Roman-Bold' => [
                'name'          => 'TimeNewRoman',
                'text'          => 'SVN Times New Roman Bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'TimeNewRoman/SVN-Times-New-Roman-Bold.ttf',
            ],
            'SVN-Times-New-Roman-Italic' => [
                'name'          => 'TimeNewRoman',
                'text'          => 'SVN Times New Roman Italic',
                'weight'        => 'regular',
                'style'         => 'italic',
                'path'          => 'TimeNewRoman/SVN-Times-New-Roman-Italic.ttf',
            ],
            'SVN-Arial-Regular' => [
                'name'          => 'Arial',
                'text'          => 'SVN Arial Regular',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'Arial/SVN-Arial-Regular.ttf',
            ],
            'SVN-Arial-Italic' => [
                'name'          => 'Arial',
                'text'          => 'SVN Arial Italic',
                'weight'        => 'regular',
                'style'         => 'italic',
                'path'          => 'Arial/SVN-Arial-Italic.ttf',
            ],
            'SVN-Arial-Bold' => [
                'name'          => 'Arial',
                'text'          => 'SVN Arial Bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'Arial/SVN-Arial-Bold.ttf',
            ],
            'SVN-Arial-Bold-Italic' => [
                'name'          => 'Arial',
                'text'          => 'SVN Arial Bold Italic',
                'weight'        => 'bold',
                'style'         => 'italic',
                'path'          => 'Arial/SVN-Arial-Bold-Italic.ttf',
            ],
            // 'Griffiths' => [
            //     'name'          => 'Griffith',
            //     'text'          => 'Griffiths',
            //     'weight'        => 'regular',
            //     'style'         => 'normal',
            //     'path'          => 'Griffith/Griffiths.ttf',
            // ],
            'TheSeasons'            => [
                'name'          => 'TheSeasons',
                'text'          => 'TheSeasons',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'TheSeasons/SVN-TheSeasons-Regular.ttf',
            ],
            'TheSeasons-bold'       => [
                'name'          => 'TheSeasons',
                'text'          => 'TheSeasons - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'TheSeasons/SVN-TheSeasons-Bold.ttf',
            ],
            'TheSeasons-italic'     => [
                'name'          => 'TheSeasons',
                'text'          => 'TheSeasons - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'italic',
                'path'          => 'TheSeasons/SVN-TheSeasons-Italic.ttf',
            ],
            'Magesta'     => [
                'name'          => 'Magesta',
                'text'          => 'Magesta regular',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'Magesta/1FTV-Magesta-1.ttf',
            ],
            'philosopher Regular'     => [
                'name'          => 'philosopher',
                'text'          => 'Philosopher regular',
                'weight'        => 'regular',
                'style'         => 'normal',
                'path'          => 'philosopher/Philosopher-Regular.ttf',
            ],
            'philosopher Bold'     => [
                'name'          => 'philosopher',
                'text'          => 'Philosopher bold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'philosopher/Philosopher-Bold.ttf',
            ],
            'ALBRA Light Italic'=> [
                'name'          => 'ALBRA',
                'text'          => 'ALBRA Light Italic',
                'weight'        => 'light',
                'style'         => 'italic',
                'path'          => 'ALBRA/1FTVVIPALBRATEXT-LIGHTITALIC.ttf',
            ],
        ];
    }

    public function getFont(string $font)
    {
        return $this->getFonts()[$font] ?? [];
    }
}
