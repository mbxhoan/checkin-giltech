<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailTemplate
 *
 * @property int $id
 * @property string|null $ref_id
 * @property string|null $uuid
 * @property string $name
 * @property string|null $subject
 * @property string|null $banner
 * @property string|null $footer
 * @property array|null $texts
 * @property string|null $html
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class EmailTemplate extends BaseModel
{
	protected $table = 'email_templates';

	protected $casts = [
		'texts' => 'json'
	];

	protected $fillable = [
		'ref_id',
		'uuid',
		'name',
		'subject',
		'banner',
		'footer',
		'texts',
		'html',
		'status'
	];
}
