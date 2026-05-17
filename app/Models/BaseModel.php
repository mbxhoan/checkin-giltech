<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class Site
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|HelpCategory[] $help_categories
 * @property Collection|Help[] $helps
 * @property Collection|Page[] $pages
 * @property Collection|PostCategory[] $post_categories
 * @property Collection|Post[] $posts
 *
 * @package App\Models
 */
class BaseModel extends Model
{
    const STATUS_ACTIVE         = 'ACTIVE';
    const STATUS_INACTIVE       = 'INACTIVE';
    const STATUS_DELETED        = 'DELETED';

    const STATUES = [
        self::STATUS_ACTIVE     => 'Active',
        self::STATUS_INACTIVE   => 'In-Active',
    ];

    const STATUS_CLASS = [
        self::STATUS_ACTIVE     => 'btn-success',
        self::STATUS_INACTIVE   => 'btn-secondary',
    ];

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

    public function getUpdatedAtAttribute($date)
    {
        return date("d-m-Y H:i:s", strtotime($date));
    }

    public function getCreatedAtAttribute($date)
    {
        return date("d-m-Y H:i:s", strtotime($date));
    }

    public function getUrlFile($dataFile)
    {
        return Storage::url($dataFile);
    }

    public static function generateUniqueCode($prefix = "UNK", $postFixLength = 8): string
    {
        do {
            $code = Helper::generateCode($prefix, $postFixLength);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public static function generateImgCode(string $code, string $prefix)
    {
        return Helper::generateImgQrcode($code, strtolower($prefix), false);
    }

    public static function deleteImgQrcode(string $code, string $prefix)
    {
        $filename = "{$code}.png";
        $folder = strtolower($prefix);
        $filePath = "public/qrcodes/{$folder}/{$filename}";

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return true;
        }

        return false;
    }

    public static function getDeletedStr(?string $suffix = null)
    {
        if ($suffix) {
            return "DELETED_AT_".date('YmdHis')."_{$suffix}";
        }

        return null;
    }

    public function getFieldAttributes()
    {
        return [
            'bold' => [
                'type'  => 'boolean',
                'input' => 'switch',
            ],
            'italic' => [
                'type'  => 'boolean',
                'input' => 'switch',
            ],
            'underline' => [
                'type'  => 'boolean',
                'input' => 'switch',
            ],
            'font' => [
                'type'  => 'text',
                'input' => 'select',
            ],
            'font_size' => [
                'type'  => 'number',
                'input' => 'number',
            ],
            'bg' => [
                'type'  => 'boolean',
                'input' => 'switch',
            ],
            'color' => [
                'type'  => 'string',
                'input' => 'color',
            ],
            'bg_color' => [
                'type'  => 'string',
                'input' => 'color',
            ],
            'stroke' => [
                'type'  => 'string',
                'input' => 'color',
            ],
            'align' => [
                'type'  => 'string',
                'input' => 'select',
            ],
            'h_align' => [
                'type'  => 'string',
                'input' => 'select',
            ],
            'width' => [
                'type'  => 'number',
                'input' => 'select',
            ],
            'height' => [
                'type'  => 'number',
                'input' => 'select',
            ],
            'pos_x' => [
                'type'  => 'number',
                'input' => 'number',
            ],
            'pos_y' => [
                'type'  => 'number',
                'input' => 'number',
            ],
        ];
    }

    public function generateCssFromAttributes(array $attributes, string $name, string $key): string
    {
        if (!count($attributes)) return "";
        $css = '<style>';
        $css .= "#{$name} {";

        foreach ($this->getFieldAttributes() as $field => $attr) {
            switch ($field) {
                case 'bold':
                    if ($attributes[$key][$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'italic':
                    if ($attributes[$key][$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'underline':
                    if ($attributes[$key][$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'font':
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$key][$field] ?? 'roboto',
                    ]);
                    break;
                case 'font_size':
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$key][$field] ?? 50,
                        'unit'  => "%",
                    ]);
                    break;
                case 'color':
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$key][$field] ?? "#000000",
                    ]);
                    break;
                case 'stroke':
                    /* nếu màu mặc định (trắng tinh) thì xem như ko có viền luôn */
                    $value = $attributes[$key][$field] ?? "#ffffff";
                    if ($value == "#ffffff") break;
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$key][$field] ?? "#ffffff",
                    ]);
                    break;
                case 'bg_color':
                    $showBg = $attributes[$key]['bg'] ?? false;

                    if ($showBg) {
                        $css .= __("styles.{$field}", [
                            'value' => $attributes[$key][$field] ?? "#ffffff",
                        ]);
                        $css .= __("styles.padding", [
                            'top'       => "2",
                            'right'     => "10",
                            'bottom'    => "2",
                            'left'      => "10",
                            'unit'      => "px",
                        ]);
                        $css .= __("styles.border_radius", [
                            'value'     => 35,
                            'unit'      => "px",
                        ]);
                    }
                    break;
                case 'width':
                    $css .= __("styles.{$field}", [
                        'value' => ($attributes[$key][$field] ?? "50")."%",
                    ]);
                    break;
                case 'pos_x':
                    $css .= __("styles.left", [
                        'value'     => ($attributes[$key][$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'pos_y':
                    $css .= __("styles.top", [
                        'value'     => ($attributes[$key][$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'align':
                    $css .= __("styles.text_align", [
                        'value' => $attributes[$key][$field] ?? 'left',
                    ]);
                    break;
                default:
                    break;
            }
        }

        $css .= '}';
        $css .= '</style>';
        return $css;
    }
}
