<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventArea
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $description
 * @property array|null $client_types
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class EventArea extends Model
{
	protected $table = 'event_areas';

	protected $casts = [
		'event_id' => 'int',
		'client_types' => 'json'
	];

	protected $fillable = [
		'event_id',
		'name',
		'description',
		'client_types',
		'note'
	];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'area_id');
    }
}
