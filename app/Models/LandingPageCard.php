<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LandingPageCard
 *
 * @property int $id
 * @property int $landing_page_id
 * @property int $card_id
 * @property string|null $lang
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class LandingPageCard extends Model
{
	use SoftDeletes;
	protected $table = 'landing_page_cards';

	protected $casts = [
		'landing_page_id' => 'int',
		'card_id' => 'int'
	];

	protected $fillable = [
		'landing_page_id',
		'card_id',
		'lang',
		'deleted_at'
	];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
