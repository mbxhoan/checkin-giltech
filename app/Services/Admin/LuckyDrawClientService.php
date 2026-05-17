<?php
namespace App\Services\Admin;

use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;
use App\Models\LuckyDrawReward;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;
use App\Services\Middleware\ClientService as MiddlewareClientService;

class LuckyDrawClientService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LuckyDrawClient::class);
    }

    public function client()
    {
        return new ClientService();
    }

    public function checkin()
    {
        return new CheckinService();
    }

    public function luckyDraw()
    {
        return new LuckyDrawService();
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function sync(LuckyDraw $luckyDraw, $filters = [])
    {
        // Lấy type từ client_type (field name trong form)
        $clientType = $filters['client_type'] ?? null;
        $attributes = [];
        
        if (!empty($clientType)) {
            $attributes['type'] = $clientType;
        }

        if ($filters['group'] == "checked") {
            $clients = $this->middleware_client()
                ->getClientCheckedIn($luckyDraw->event->code, array_filter($attributes));
        } else {
            // Query cơ bản theo event_id
            $queryAttributes = [
                'event_id' => $luckyDraw->event->id
            ];
            
            // Thêm filter theo type nếu có chọn nhóm khách
            if (!empty($clientType)) {
                $queryAttributes['type'] = $clientType;
            }
            
            $clients = $this->client()->getListByAttributes($queryAttributes);
        }

        try {
            if (!empty($clients) && $clients->count()) {
                $this->reset($luckyDraw);
            }

            foreach ($clients as $client) {
                // Decode custom_fields if it's a JSON string (from Query Builder)
                $customFields = $client->custom_fields ?? null;
                if (is_string($customFields)) {
                    $customFields = json_decode($customFields, true);
                }

                $attributes = [
                    'id'            => null,
                    'lucky_draw_id' => $luckyDraw->id,
                    'name'          => $client->name,
                    'qrcode'        => $client->qrcode,
                    'email'         => $client->email ?? null,
                    'custom_fields' => $customFields,
                    'type'          => $client->type ?? null,
                    'status'        => LuckyDrawClient::STATUS_ACTIVE,
                ];
                $this->create($attributes);
            }

            return [
                'success'   => true,
                'msg'       => "Đồng bộ thành công"
            ];
        } catch (\Exception $e) {
            Log::info("Lỗi cập nhật danh sách tham dự lucky draw #{$luckyDraw->id}: {$e}");
            return [
                'success'   => false,
                'msg'       => $e->getMessage()
            ];
        }

        return [
            'success'       => false,
            'msg'           => "Đồng bộ KHÔNG thành công"
        ];
    }

    public function reset(LuckyDraw $luckyDraw)
    {
        $clients = $this->getListByAttributes([
            'lucky_draw_id' => $luckyDraw->id
        ]);

        if ($clients) {
            foreach ($clients as $client) {
                $client->delete();
            }

            return true;
        }

        return false;
    }

    public function resetRewards(LuckyDraw $luckyDraw)
    {
        $clients = $this->getListByAttributes([
            'lucky_draw_id' => $luckyDraw->id
        ], [
            'reward_id'     => null
        ]);

        if (!empty($clients) && $clients->count()) {
            foreach ($clients as $client) {
                $this->resetReward($client);
            }
        }

        return true;
    }

    public function resetReward(LuckyDrawClient $client)
    {
        $this->update($client->id, [
            'reward_id' => null
        ]);
        return true;
    }

    public function removeAssignedReward(LuckyDrawReward $lucky_draw_reward)
    {
        $clients = $this->getListByAttributes([
            'reward_id' => $lucky_draw_reward->id
        ]);

        if (!empty($clients) && $clients->count()) {
            foreach ($clients as $client) {
                $this->update($client->id, [
                    'reward_id' => null
                ]);
            }
        }

        return true;
    }
}
