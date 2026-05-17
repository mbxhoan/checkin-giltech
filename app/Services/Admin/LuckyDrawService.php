<?php
namespace App\Services\Admin;

use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;
use App\Models\LuckyDrawReward;
use App\Services\BaseService;
use App\Services\Middleware\ClientService as MiddlewareClientService;

class LuckyDrawService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LuckyDraw::class);
    }

    public function luckyDrawReward()
    {
        return new LuckyDrawRewardService();
    }

    public function luckyDrawClient()
    {
        return new LuckyDrawClientService();
    }

    public function company()
    {
        return new CompanyService();
    }

    public function event()
    {
        return new EventService();
    }

    public function client()
    {
        return new ClientService();
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function updateRaffle()
    {
        $rewardId = $this->attributes['reward_id'];
        $clientIds = $this->attributes['client_ids'] ?? [];
        if (!is_array($clientIds)) {
            $clientIds = [$clientIds];
        }

        foreach ($clientIds as $clientId) {
            $modelClient = LuckyDrawClient::find($clientId);
            if ($modelClient) {
                $modelClient->update([
                    'reward_id' => $rewardId
                ]);
            }
        }

        // Đếm số người đã trúng giải này
        $modelReward = LuckyDrawReward::find($rewardId);
        if ($modelReward) {
            $winnerCount = LuckyDrawClient::where('reward_id', $rewardId)->count();
            $maxWinners = $modelReward->value;
            if ($winnerCount >= $maxWinners) {
                $modelReward->update([
                    'is_given' => true
                ]);
            }
            return true;
        }

        return false;
    }

    public function updateSpinWheel()
    {
        $clientId = $this->attributes['client_id'];
        $rewardId = $this->attributes['reward_id'];
        $modelClient = LuckyDrawClient::find($clientId);

        if ($modelClient) {
            $modelReward = LuckyDrawReward::find($rewardId);

            if ($modelReward) {
                $modelClient->update([
                    'reward_id' => $rewardId
                ]);

                $modelReward->update([
                    'assignee_id' => $modelClient->id,
                ]);

                return true;
            }
        }

        return false;
    }

    public function resetRewardClients($luckyDrawId)
    {
        LuckyDrawClient::where('lucky_draw_id', $luckyDrawId)
                   ->whereNotNull('reward_id')
                   ->update(['reward_id' => null]);

        LuckyDrawReward::where('lucky_draw_id', $luckyDrawId)->update(['assignee_id' => null, 'is_given' => 0]);

    }
}
