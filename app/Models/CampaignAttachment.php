<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $event_file_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $name
 * @property string $file_path
 * @property string $mime
 * @property string $created_at
 * @property string $updated_at
 * @property Admin $admin
 * @property EventFile $eventFile
 * @property Admin $admin
 */
class CampaignAttachment extends Model
{
    protected $table = 'campaign_attachments';

    /**
     * @var array
     */
    protected $fillable = [
        'event_file_id',
        'created_by',
        'updated_by',
        'name',
        'file_path',
        'mime',
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventFile()
    {
        return $this->belongsTo(EventFile::class);
    }

    public function created_by()
	{
        return $this->belongsTo(Admin::class, 'created_by');
	}

    public function updated_by()
	{
        return $this->belongsTo(Admin::class, 'updated_by');
	}
}
