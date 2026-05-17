<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PageAccessLog
 * 
 * @property int $id
 * @property int|null $lp_id
 * @property string $page
 * @property string|null $ip_address
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PageAccessLog extends Model
{
	protected $table = 'page_access_logs';

	protected $casts = [
		'lp_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'lp_id',
		'page',
		'ip_address',
		'user_id'
	];
}
