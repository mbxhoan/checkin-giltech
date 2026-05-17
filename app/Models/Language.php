<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|LanguageDefine[] $language_defines
 *
 * @package App\Models
 */
class Language extends BaseModel
{
	protected $table = 'languages';

	protected $fillable = [
		'code',
		'name',
		'icon_path',
		'description',
		'status'
	];

	public function languageDefines()
	{
		return $this->hasMany(LanguageDefine::class);
	}

    public function events()
	{
		return $this->belongsToMany(Event::class, 'event_languages')
					->withPivot('id')
					->withTimestamps();
	}
}
