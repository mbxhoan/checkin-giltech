<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventFileLog
 * 
 * @property int $id
 * @property int|null $event_id
 * @property string|null $event_code
 * @property string $name
 * @property string $path
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class EventFileLog extends BaseModel
{
	protected $table = 'event_file_logs';

	protected $casts = [
		'event_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'event_code',
		'name',
		'path',
		'type'
	];
}
