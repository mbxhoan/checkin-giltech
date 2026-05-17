<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class LandingPageCampaign
 *
 * @property int $id
 * @property int $landing_page_id
 * @property int $campaign_id
 * @property string|null $lang
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class LandingPageCampaign extends Model
{
	use SoftDeletes;
	protected $table = 'landing_page_campaigns';

	protected $casts = [
		'landing_page_id' => 'int',
		'campaign_id' => 'int'
	];

	protected $fillable = [
		'landing_page_id',
		'campaign_id',
		'lang',
		'deleted_at'
	];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
