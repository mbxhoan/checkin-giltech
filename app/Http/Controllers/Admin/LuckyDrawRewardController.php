<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\Admin\LuckyDrawRewardService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuckyDrawRewards\LuckyDrawRewardRequest;
use App\Models\LuckyDrawReward;
use App\Models\LuckyDraw;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LuckyDrawRewardController extends Controller
{
    public function __construct(LuckyDrawRewardService $service)
    {
        $this->service = $service;
    }

    public function store(LuckyDraw $lucky_draw, LuckyDrawRewardRequest $request)
    {
        $attributes = $request->only('reward')['reward'];
        $attributes['lucky_draw_id'] = $lucky_draw->id;
        $attributes['status'] = LuckyDrawReward::STATUS_ACTIVE;
        $this->service->create($attributes);
        return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
                ->withSuccess("Đã thêm mới giải thưởng");
    }

    public function update(LuckyDrawReward $lucky_draw_reward, LuckyDrawRewardRequest $request)
    {
        $attributes = $request->only('reward')['reward'];
        unset($attributes['code']);
        $this->service->update($lucky_draw_reward->id, $attributes);
        return redirect()->route('admin.lucky_draws.edit', $lucky_draw_reward->lucky_draw)
                ->withSuccess("Đã cập nhật giải thưởng");
    }

    public function upload(LuckyDraw $lucky_draw, Request $request)
    {
        $request->validate([
            'file'          => "required|file|max:".config('app.upload_data_size_max')."|mimes:".config('app.upload_data_allow_types'),
        ]);

        try {
            $file = $request->file('file');
            $storePath = storage_path("app/import-lucky-draw-rewards/{$lucky_draw->id}");
            Storage::deleteDirectory("import-lucky-draw-rewards/{$lucky_draw->id}");
            $fileName = date('Ymd-his').'.'.$file->extension();
            $file->move($storePath, $fileName);
            if ($this->service->upload($lucky_draw, "{$storePath}/{$fileName}")) {
                return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
                    ->withSuccess("Đã nạp file giải thưởng thành công");
            }

        } catch (\Exception $e) {
            Log::alert($e);

            if (auth()->user()->isSysAdmin()) {
                $msgError = $e->getMessage();
            }
        }

        return back()->withErrors($msgError ?? "Không thể nạp file");
    }

    public function destroy(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        if ($this->service->destroy($lucky_draw_reward)) {
            return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                ->withSuccess('Giải thưởng đã được xóa');
        }

        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                ->withErrors('Đã có lỗi xảy ra');
    }

    public function destroyAll(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:RESET'],
        ]);

        try {
            $this->service->destroyAll($lucky_draw_reward);
            return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                ->withSuccess('Tất cả giải thưởng đã được xóa');
        } catch (\Exception $e) {
            if (auth()->user()->isSysAdmin()) {
                return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                    ->withErrors($e->getMessage());
            }
        }

        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                    ->withErrors("Reset giải thưởng KHÔNG thành công");
    }

    public function destroyAllByLuckyDraw(LuckyDraw $lucky_draw, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:RESET'],
        ]);

        try {
            $this->service->destroyAll($lucky_draw);
            return $this->responseSuccess('Tất cả giải thưởng đã được xóa');
        } catch (\Exception $e) {
            if (auth()->user()->isSysAdmin()) {
                return $this->responseError($e->getMessage());
            }
        }

        return $this->responseError("Reset giải thưởng KHÔNG thành công");
    }

    public function resetAssigness(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:RESET'],
        ]);

        try {
            $this->service->resetAssigness($lucky_draw_reward);
            return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                ->withSuccess('Làm mới thông tin nhận giải thành công');
        } catch (\Exception $e) {
            if (auth()->user()->isSysAdmin()) {
                return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                    ->withErrors($e->getMessage());
            }
        }

        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                    ->withErrors("Làm mới thông tin nhận giải KHÔNG thành công");
    }

    public function cancelReward(LuckyDrawReward $lucky_draw_reward)
    {
        $cancel = $this->service->cancelReward($lucky_draw_reward);

        if ($cancel) {
            return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                ->withSuccess('Đã huỷ giải thành công');
        }

        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw)
                    ->withErrors("Huỷ giải KHÔNG thành công");
    }

    public function updateAssignee(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        $assigneeId = $request->input('assignee_id');
        
        $this->service->update($lucky_draw_reward->id, [
            'assignee_id' => $assigneeId ?: null
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật người được gán thành công'
            ]);
        }

        return redirect()->route('admin.lucky_draws.edit', $lucky_draw_reward->lucky_draw)
            ->withSuccess("Đã cập nhật người được gán thành công");
    }

    /**
     * Cập nhật danh sách người được gán (nhiều assignee) cho 1 giải.
     */
    public function updateAssignees(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        $assigneeIds = $request->input('assignee_ids', []);
        if (!is_array($assigneeIds)) {
            $assigneeIds = [$assigneeIds];
        }

        // Normalize: ints, remove empty, unique
        $assigneeIds = array_values(array_unique(array_filter(array_map(function ($v) {
            if ($v === null || $v === '') return null;
            return (int) $v;
        }, $assigneeIds))));

        $lucky_draw_reward->assignees()->sync($assigneeIds);

        // Giữ tương thích ngược với logic cũ (1 assignee_id)
        $this->service->update($lucky_draw_reward->id, [
            'assignee_id' => $assigneeIds[0] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật danh sách người được gán thành công'
            ]);
        }

        return redirect()->route('admin.lucky_draws.edit', $lucky_draw_reward->lucky_draw)
            ->withSuccess("Đã cập nhật danh sách người được gán thành công");
    }
}
