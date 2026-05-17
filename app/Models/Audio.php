<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Audio
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $code
 * @property string $text
 * @property string|null $file_path
 * @property string|null $link
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Company|null $company
 * @property User|null $user
 *
 * @package App\Models
 */
class Audio extends Model
{
	protected $table = 'audios';

	protected $casts = [
		'company_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'company_id',
		'code',
		'text',
		'voice',
		'file_path',
		'link',
		'created_by',
		'updated_by'
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

    public static function generateUniqueCode(int $limit = 20): string
    {
        $baseCode = Helper::randomCode(8, true, false);
        $baseCode = Str::limit($baseCode, $limit, '');
        $code = $baseCode;
        $suffix = 1;

        while (Audio::where('code', $code)->exists()) {
            $code = Str::limit($baseCode, $limit - (strlen((string) $suffix) + 1), '') . '-' . $suffix;
            $suffix++;
        }

        return $code;
    }

    public static function getAITTtsModel()
    {
        return [
            'alloy'     => 'alloy',
            'ash'       => 'ash',
            'ballad'    => 'ballad',
            'coral'     => 'coral',
            'echo'      => 'echo',
            'fable'     => 'fable',
            'nova'      => 'nova',
            'onyx'      => 'onyx',
            'sage'      => 'sage',
            'shimmer'   => 'shimmer',
        ];
    }
}
