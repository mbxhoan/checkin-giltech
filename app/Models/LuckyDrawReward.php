<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LuckyDrawReward
 *
 * @property int $id
 * @property int|null $lucky_draw_id
 * @property string $name
 * @property string $img_link
 * @property string|null $value
 * @property int|null $order
 * @property float|null $probability
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property LuckyDraw|null $lucky_draw
 * @property Collection|LuckyDrawClient[] $lucky_draw_clients
 *
 * @package App\Models
 */
class LuckyDrawReward extends BaseModel
{
	protected $table = 'lucky_draw_rewards';

	protected $casts = [
		'lucky_draw_id' => 'int',
		'order' => 'int',
		'probability' => 'float'
	];

	protected $fillable = [
        'assignee_id',
		'lucky_draw_id',
        'is_given',
		'code',
		'name',
		'img_link',
		'value',
		'order',
        'order_name',
        'time',
		'probability',
		'status'
	];

	public function lucky_draw()
	{
		return $this->belongsTo(LuckyDraw::class);
	}

	public function clients()
	{
		return $this->hasMany(LuckyDrawClient::class, 'reward_id');
	}

        /**
         * Những khách hàng được gán trước (assignee) cho giải này.
         * Cho phép 1 giải có nhiều người được cơ cấu trước.
         */
        public function assignees()
        {
            return $this->belongsToMany(
                LuckyDrawClient::class,
                'lucky_draw_reward_assignees',
                'reward_id',
                'lucky_draw_client_id'
            )->withTimestamps();
        }
}
