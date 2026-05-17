<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LuckyDrawClient
 *
 * @property int $id
 * @property int|null $reward_id
 * @property int $lucky_draw_id
 * @property string $name
 * @property string $qrcode
 * @property string|null $email
 * @property string|null $phone
 * @property string $type
 * @property string $status
 * @property array|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property LuckyDraw $lucky_draw
 * @property LuckyDrawReward|null $lucky_draw_reward
 *
 * @package App\Models
 */
class LuckyDrawClient extends BaseModel
{
	protected $table = 'lucky_draw_clients';

	protected $casts = [
		'reward_id' => 'int',
		'lucky_draw_id' => 'int',
		'custom_fields' => 'json'
	];

	protected $fillable = [
		'reward_id',
		'lucky_draw_id',
		'name',
		'qrcode',
		'email',
		'phone',
		'type',
		'status',
		'custom_fields'
	];

	public function lucky_draw()
	{
		return $this->belongsTo(LuckyDraw::class);
	}

	public function reward()
	{
		return $this->belongsTo(LuckyDrawReward::class, 'reward_id');
	}

        /**
         * Danh sách các giải mà khách này được “cơ cấu trước” (assignee).
         */
        public function assignedRewards()
        {
            return $this->belongsToMany(
                LuckyDrawReward::class,
                'lucky_draw_reward_assignees',
                'lucky_draw_client_id',
                'reward_id'
            )->withTimestamps();
        }
}
