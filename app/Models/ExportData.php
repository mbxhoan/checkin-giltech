<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExportData
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string $status
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 * @property User $user
 *
 * @package App\Models
 */
class ExportData extends BaseModel
{
	protected $table = 'export_datas';

	protected $casts = [
		'event_id' 	=> 'int',
		'user_id' 	=> 'int'
	];

	protected $fillable = [
		'event_id',
		'admin_id',
		'name',
		'file_path',
		'file_name',
		'status',
		'type'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function admin()
	{
		return $this->belongsTo(Admin::class);
	}
}
