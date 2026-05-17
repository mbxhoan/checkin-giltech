<?php
namespace App\Services\Admin;

use App\Imports\LuckyDrawRewardImport;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawReward;
use App\Services\BaseService;
use Maatwebsite\Excel\Facades\Excel;

class LuckyDrawRewardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LuckyDrawReward::class);
    }

    public function lucky_draw()
    {
        return new LuckyDrawService();
    }

    public function lucky_draw_client()
    {
        return new LuckyDrawClientService();
    }

    public function upload(LuckyDraw $luckyDraw, $filePath)
    {
        $luckyDraw->touch();
        Excel::import(new LuckyDrawRewardImport($luckyDraw), $filePath);
        return true;
    }

    public function destroy(LuckyDrawReward $lucky_draw_reward)
    {
        if ($this->lucky_draw_client()->removeAssignedReward($lucky_draw_reward)) {
            $this->delete($lucky_draw_reward->id);
            return true;
        }

        return false;
    }

    public function destroyAll(LuckyDraw $luckyDraw)
    {
        if ($this->lucky_draw_client()->resetRewards($luckyDraw)) {
            $rewards =  $this->getListByAttributes([
                'lucky_draw_id' => $luckyDraw->id
            ]);

            if (!empty($rewards) && $rewards->count()) {
                foreach ($rewards as $reward) {
                    $this->delete($reward->id);
                }
            }
        }

        return false;
    }

    public function resetAssigness(LuckyDraw $luckyDraw)
    {
        if ($this->lucky_draw_client()->resetRewards($luckyDraw)) {
            $rewards =  $this->getListByAttributes([
                'lucky_draw_id' => $luckyDraw->id
            ]);

            if (!empty($rewards) && $rewards->count()) {
                foreach ($rewards as $reward) {
                    $this->update($reward->id, [
                        'assignee_id'   => null,
                        'is_given'      => 0
                    ]);
                }
            }
        }

        return false;
    }

    public function cancelReward(LuckyDrawReward $reward)
    {
        if ($reward->is_given) {
            if (!empty($reward->clients) && $reward->clients->count()) {
                foreach ($reward->clients as $client) {
                    $this->lucky_draw_client()->resetReward($client);
                }
            }

            $this->update($reward->id, [
                'assignee_id'   => null,
                'is_given'      => 0,
            ]);

            return true;
        }

        return false;
    }
}
