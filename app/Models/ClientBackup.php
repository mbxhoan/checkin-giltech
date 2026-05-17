<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientBackup
 *
 * @property int $id
 * @property int $event_id
 * @property int $country_id
 * @property int $org_id
 * @property string $event_code
 * @property string|null $qrcode
 * @property string $name
 * @property string|null $email
 * @property string|null $type
 * @property string|null $register_source
 * @property array|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ClientBackup extends Model
{
	protected $table = 'client_backups';

	protected $casts = [
		'event_id' => 'int',
		'country_id' => 'int',
		'org_id' => 'int',
		'custom_fields' => 'json'
	];

	protected $fillable = [
		'event_id',
		'country_id',
		'org_id',
		'event_code',
		'qrcode',
		'name',
		'email',
		'type',
		'register_source',
		'custom_fields',
        'batch_key',
	];
}
