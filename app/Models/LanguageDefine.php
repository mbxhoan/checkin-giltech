<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LanguageDefine
 *
 * @property int $id
 * @property int $event_id
 * @property int $language_id
 * @property string $keyword
 * @property string $translate
 * @property string $type
 * @property array|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 * @property Language $language
 *
 * @package App\Models
 */
class LanguageDefine extends BaseModel
{
	protected $table = 'language_defines';

    const TYPE_TEXT = 'TEXT';
    const TYPE_HTML = 'HTML';

    const TYPE = [
        self::TYPE_TEXT => 'Văn bản',
        self::TYPE_HTML => 'Html',
    ];

	protected $casts = [
		'event_id' => 'int',
		'language_id' => 'int',
		'value' => 'json'
	];

	protected $fillable = [
		'event_id',
		'language_id',
		'keyword',
		'translate',
		'type',
		'value',
		'status'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function language()
	{
		return $this->belongsTo(Language::class);
	}

    static public function getTypes()
    {
        return self::TYPE;
    }

    public function getTypeText()
    {
        return self::TYPE[$this->status];
    }
}
