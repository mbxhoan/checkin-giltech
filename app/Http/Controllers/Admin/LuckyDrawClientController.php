<?php
namespace App\Http\Controllers\Admin;

use App\Services\Admin\LuckyDrawClientService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuckyDrawClients\SyncRequest;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;

class LuckyDrawClientController extends Controller
{
    public function __construct(LuckyDrawClientService $service)
    {
        $this->service = $service;
    }

    public function sync(LuckyDraw $lucky_draw, SyncRequest $request)
    {
        $filters = $request->all();
        $result = $this->service->sync($lucky_draw, $filters);

        if ($result['success']) {
            return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
                ->withSuccess($result['msg']);
        }

        return back()->withSuccess($result['msg']);
    }

    public function reset(Request $request)
    {
        if ($request->ajax()) {
            $luckyDraw = LuckyDraw::find($request->lucky_draw_id);

            if (!$luckyDraw) {
                return $this->responseError("Không tìm thấy Lucky Draw");
            }

            // Reset cả reward_id của clients và assignee_id của rewards
            $rewardService = app(\App\Services\Admin\LuckyDrawRewardService::class);
            $rewardService->resetAssigness($luckyDraw);

            return $this->responseSuccess("Reset kết quả quay thành công!");
        }
    }

    public function destroy(LuckyDrawClient $lucky_draw_client)
    {
        // $this->service->delete($lucky_draw_client->id);
        $this->service->update($lucky_draw_client->id, [
            'status' => LuckyDrawClient::STATUS_DELETED
        ]);
        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_client->lucky_draw)
            ->withSuccess('Khách hàng đã được xóa');
    }
}
