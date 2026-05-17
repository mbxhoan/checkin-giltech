<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ImpexpFile
 *
 * @property int $id
 * @property int|null $event_id
 * @property string $name
 * @property string $file_path
 * @property int $total_record_before
 * @property int $total_record
 * @property string $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event|null $event
 *
 * @package App\Models
 */
class ImpexpFile extends Model
{
	protected $table = 'impexp_files';

    const STATUS_NEW        = 'NEW';
    const STATUS_IMPORTED   = 'IMPORTED';
    const STATUS_EXPORTED   = 'EXPORTED';

    const STATUSES = [
        self::STATUS_NEW        => 'File mới',
        self::STATUS_IMPORTED   => 'Đã Import',
    ];

    const TYPE_IMPORT   = 'IMPORT';
    const TYPE_EXPORT   = 'EXPORT';

    const FILE_EXCEL    = 'EXCEL';
    const FILE_CSV      = 'CSV';

	protected $casts = [
		'event_id' => 'int',
		'total_record_before' => 'int',
		'total_record' => 'int'
	];

	protected $fillable = [
		'event_id',
		'name',
		'table',
		'file_path',
		'total_record_before',
		'total_record',
        'error_log',
		'type',
		'status'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}
}
