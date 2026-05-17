<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventFile
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $media_id
 * @property string $name
 * @property string $file_path
 * @property bool $is_public
 * @property string $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Event $event
 * @property Medium|null $medium
 * @property Collection|CampaignAttachment[] $campaign_attachments
 *
 * @package App\Models
 */
class EventFile extends BaseModel
{
	protected $table = 'event_files';

	protected $casts = [
		'event_id' => 'int',
		'media_id' => 'int',
		'is_public' => 'bool'
	];

	protected $fillable = [
		'event_id',
		'media_id',
		'name',
		'file_path',
		'is_public',
		'type',
		'status'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

	public function campaign_attachments()
	{
		return $this->hasMany(CampaignAttachment::class);
	}
}
