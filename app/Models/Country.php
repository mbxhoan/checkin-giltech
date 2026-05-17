<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string|null $link_flag
 * @property string|null $alt
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Country extends BaseModel
{
	protected $table = 'countrys';

	protected $fillable = [
		'code',
		'name',
		'is_default',
		'description',
		'link_flag',
		'alt'
	];
}
