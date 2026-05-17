<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LabelDetail
 *
 * @property int $id
 * @property int $label_id
 * @property string $field
 * @property float $pos_x
 * @property float $pos_y
 * @property string $v_align
 * @property string $h_align
 * @property string $color
 * @property string|null $font
 * @property float $size
 * @property string $unit
 * @property string $width
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Label $label
 *
 * @package App\Models
 */
class LabelDetail extends BaseModel
{
	protected $table = 'label_details';

    const STATUES = [
        self::STATUS_ACTIVE     => 'Hiển thị',
        self::STATUS_INACTIVE   => 'Ẩn',
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
        self::V_ALIGN_TOP       => 'Trên',
        self::V_ALIGN_center    => 'Giữa',
        self::V_ALIGN_BOTTOM    => 'Dưới',
    ];

    const TYPE_FIELD    = 'FIELD';
    const TYPE_IMG      = 'IMG';

    const TYPES = [
        self::TYPE_FIELD    => 'Trường thông tin',
        self::TYPE_IMG      => 'Ảnh',
    ];

	protected $casts = [
		'label_id'  => 'int',
		'pos_x'     => 'float',
		'pos_y'     => 'float',
		'size'      => 'float'
	];

	protected $fillable = [
		'label_id',
		'field',
        'type',
		'value',
		'pos_x',
		'pos_y',
		'v_align',
		'h_align',
		'color',
		// 'font',
        'bold',
        'italic',
        'uppercase',
		'size',
		'unit',
		'width',
		'status'
	];

	public function label()
	{
		return $this->belongsTo(Label::class);
	}

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

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function getTypesText()
    {
        return self::TYPES[$this->type];
    }
}
