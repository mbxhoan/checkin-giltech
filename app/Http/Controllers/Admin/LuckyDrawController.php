<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\Admin\LuckyDrawClientDataTable;
use App\DataTables\Admin\LuckyDrawDataTable;
use App\Services\Admin\LuckyDrawService;
use App\Exports\LuckyDraw\Raffle\ResultExport;
use App\Http\Requests\Admin\LuckyDraws\ListRequest;
use App\Http\Requests\Admin\LuckyDraws\LuckyDrawRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use App\Models\Client;
use App\Models\Event;
use App\Models\LuckyDraw;
use Maatwebsite\Excel\Facades\Excel;

class LuckyDrawController extends Controller
{
    public function __construct(LuckyDrawService $service)
    {
        $this->service = $service;
    }

    public function selectEventToCreate(SelectEventToCreateRequest $request)
    {
        return redirect()->route('admin.lucky_draws.create', [
            'event' => $request->event_id
        ]);
    }

    public function index(ListRequest $request)
    {
        $dataTable = new LuckyDrawDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();

        return $dataTable->render('admin.lucky_draws.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
        ]);
    }

    public function create(Event $event)
    {
        $this->authorize('create_lucky_draw', $event);

        $clients = $this->service->client()->getListByAttributes([
            'event_id' => $event->id
        ]);
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($event->code);
        $groups = [
            "all"       => "- Tất cả ({$clients->count()}) -",
            "checked"   => "Đã checkin ({$totalCheckedIn->count()})"
        ];

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        return view('admin.lucky_draws.detail', [
            'luckyDraws'        => $this->service->getListByAttributes([
                'event_id'      => $event->id
            ], [], [], 0, []),
            'model'             => $this->service->init(),
            'event'             => $event,
            'types'             => $types,
            'groups'            => $groups,
        ]);
    }

    public function edit(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);

        $clients = $this->service->client()->getListByAttributes([
            'event_id' => $lucky_draw->event->id
        ]);
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($lucky_draw->event->code);
        $groups = [
            "all"       => "- Tất cả ({$clients->count()}) -",
            "checked"   => "Đã checkin ({$totalCheckedIn->count()})"
        ];

        // Lấy types từ bảng lucky_draw_clients (dữ liệu đã đồng bộ)
        $types = $this->service->luckyDrawClient()->getListDistinctField([
            'lucky_draw_id' => $lucky_draw->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            // Đếm số lượng từ bảng lucky_draw_clients
            $count = $this->service->luckyDrawClient()->getListByAttributes([
                'lucky_draw_id' => $lucky_draw->id,
                'type'          => $key,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $luckyDrawRewards = $this->service->luckyDrawReward()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id
        ], [], [], 0, [
            "order"         => "ASC"
        ]);
        // Preload assignees (multi) to avoid N+1 in view
        if (!empty($luckyDrawRewards) && $luckyDrawRewards->count()) {
            $luckyDrawRewards->load('assignees');
        }

        $luckyDrawClients = $this->service->luckyDrawClient()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id
        ], [], [], 0, [
            "reward_id"     => "DESC"
        ]);

        $luckyDrawClientShuffle = (clone $luckyDrawClients)->shuffle();
        $luckyDrawClientsNoRewards = (clone $luckyDrawClients)->whereNull('reward_id');

        $assignees = ["" => " - "] + $luckyDrawClientsNoRewards->map(function ($client) {
            return [
                'id'        => $client->id,
                'name'      => "{$client->qrcode} - {$client->name}",
            ];
        })->pluck('name', 'id')->toArray();

        $dataTable = new LuckyDrawClientDataTable($lucky_draw);

        return $dataTable->render('admin.lucky_draws.detail', [
            'luckyDraws'                => $this->service->getListByAttributes([
                'event_id'              => $lucky_draw->event->id
            ], [], [], 0, []),
            'model'                     => $lucky_draw,
            'event'                     => $lucky_draw->event,
            'types'                     => $types,
            'groups'                    => $groups,
            'luckyDrawRewards'          => $luckyDrawRewards,
            'luckyDrawClients'          => $luckyDrawClients,
            'luckyDrawClientShuffle'    => $luckyDrawClientShuffle,
            'assignees'                 => $assignees,
        ]);
    }

    public function store(LuckyDrawRequest $request)
    {
        $attributes = $request->only([
            'event_id',
            'name',
            'type',
        ]);

        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = LuckyDraw::STATUS_ACTIVE;
        $luckyDraw = $this->service->create($attributes);
        $medias = $request->only(array_keys($luckyDraw->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($media) {
                    $this->service->attributes['image'] = $media;
                    $this->service->attributes['name'] = $media->getClientOriginalName();

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($luckyDraw->id, [
                                $key => $result['media']->id
                            ]);
                        } else {
                            return redirect()->route('admin.landing_pages.edit', [
                                'event'         => $luckyDraw->event,
                                'landing_page'  => $luckyDraw,
                            ])->withErrors($result['msg']);
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.lucky_draws.edit', $luckyDraw)->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LuckyDrawRequest $request, LuckyDraw $lucky_draw)
    {
        $attributes = $request->only([
            'event_id',
            'name',
            'type',
        ]);

        $medias = $request->only(array_keys($lucky_draw->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($media) {
                    $this->service->attributes['image'] = $media;
                    $this->service->attributes['name'] = $media->getClientOriginalName();

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($lucky_draw->id, [
                                $key => $result['media']->id
                            ]);
                        } else {
                            return redirect()->route('admin.landing_pages.edit', [
                                'event'         => $lucky_draw->event,
                                'landing_page'  => $lucky_draw,
                            ])->withErrors($result['msg']);
                        }
                    }
                }
            }
        }

        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = LuckyDraw::STATUS_ACTIVE;
        $this->service->update($lucky_draw->id, $attributes);
        return redirect()->route('admin.lucky_draws.edit', $lucky_draw)->withSuccess("Cập nhật thành công");
    }

    public function viewRaffle(LuckyDraw $lucky_draw)
    {
        // Get rewards that haven't been given yet (is_given = false)
        $luckyDrawRewards = $this->service->luckyDrawReward()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id,
            'is_given'      => false,
        ], [], [], 0, [
            'order' => 'DESC'
        ]);

        // Preload multi-assignees to use on raffle views
        if (!empty($luckyDrawRewards) && $luckyDrawRewards->count()) {
            $luckyDrawRewards->load('assignees');
        }

        // Get all clients for this lucky draw
        $allClients = $this->service->luckyDrawClient()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id,
        ]);

        // Determine current reward (first not-given, ordered as above)
        $currentReward = $luckyDrawRewards->first();

        // Tập các client đang được cơ cấu cho NHỮNG giải KHÁC (chưa quay) để loại ra khỏi các giải hiện tại
        $otherRewards = $luckyDrawRewards->where('id', '!=', optional($currentReward)->id);
        $otherAssigneeIds = collect();

        foreach ($otherRewards as $reward) {
            // Multi-assignees via pivot
            if ($reward->relationLoaded('assignees')) {
                $otherAssigneeIds = $otherAssigneeIds->merge($reward->assignees->pluck('id'));
            }
            // Legacy single assignee_id
            if (!empty($reward->assignee_id)) {
                $otherAssigneeIds->push((int) $reward->assignee_id);
            }
        }

        $otherAssigneeIds = $otherAssigneeIds->unique()->values();

        // Filter clients:
        //  - chỉ lấy client chưa trúng (reward_id null)
        //  - loại các client đang được cơ cấu cho giải khác (otherAssigneeIds),
        //    nhưng luôn GIỮ lại client là assignee của chính currentReward.
        $luckyDrawClients = $allClients
            ->whereNull('reward_id')
            ->filter(function ($client) use ($otherAssigneeIds, $currentReward) {
                if (!$currentReward) {
                    // Không xác định được giải hiện tại thì chỉ loại theo otherAssigneeIds
                    return !$otherAssigneeIds->contains($client->id);
                }

                $isAssigneeOfCurrent = false;

                // Multi-assignees của giải hiện tại
                if ($currentReward->relationLoaded('assignees')) {
                    $isAssigneeOfCurrent = $currentReward->assignees->contains('id', $client->id);
                }

                // assignee_id đơn (tương thích ngược)
                if (!$isAssigneeOfCurrent && !empty($currentReward->assignee_id)) {
                    $isAssigneeOfCurrent = ((int) $currentReward->assignee_id === (int) $client->id);
                }

                // Nếu là assignee của giải hiện tại thì luôn được giữ lại,
                // ngược lại thì loại nếu nằm trong danh sách assignee của các giải khác.
                if ($isAssigneeOfCurrent) {
                    return true;
                }

                return !$otherAssigneeIds->contains($client->id);
            })
            ->shuffle();

        // Get winners (clients with rewards)
        $luckyDrawWinners = $allClients->whereNotNull('reward_id');

        // Map clients to the format expected by the view
        $luckyDrawClients = $luckyDrawClients->map(function ($client) {
            $phongban = $client->custom_fields['phongban'] ?? null;
            // if ($phongban) {
            //     $words = preg_split('/\s+/', trim($phongban));
            //     if (count($words) > 10) {
            //         $firstPart = implode(' ', array_slice($words, 0, 4));
            //         $lastPart = implode(' ', array_slice($words, -6));
            //         $phongban = $firstPart . ' ... ' . $lastPart;
            //     }
            // }

            return [
                'id'             => $client->id,
                'name'           => $client->name,
                'qrcode'         => $client->qrcode,
                'uid'            => $client->custom_fields['uid'] ?? null,
                'congty'         => $client->custom_fields['congty'] ?? null,
                'company'        => $client->company ?? $client->custom_fields['company'] ?? null,
                'daily'          => $client->custom_fields['daily'] ?? null,
                'phone'          => $client->phone,
                'phone_display'  => $client->phone ? substr($client->phone, -4) : null,
                'khu_vuc'        => $client->custom_fields['khu_vuc'] ?? null,
                'showroom'       => $client->custom_fields['showroom'] ?? null,
                'booking_number' => $client->custom_fields['booking_number'] ?? null,
                'stt'            => $client->custom_fields['stt'] ?? null,
                'phongban'       => $phongban,
                'type'           => $client->type ?? null,
                'manv'           => $client->custom_fields['manv'] ?? null,
                'chucvu'         => $client->custom_fields['chucvu'] ?? null,
                'no'             => $client->custom_fields['no'] ?? null,
                'email'          => $client->email,
                'department'     => $client->custom_fields['department'] ?? null,
                'don_vi_mot'     => $client->custom_fields['don_vi_mot'] ?? null,
                'don_vi_hai'     => $client->custom_fields['don_vi_hai'] ?? null,
            ];
        });

        $eventCode = $lucky_draw->event->code;
        $viewPath = "backend.lucky-draw.raffle.customs.{$eventCode}";

        if (view()->exists($viewPath)) {
            return view($viewPath, [
                'luckyDraw'         => $lucky_draw,
                'luckyDrawRewards'  => $luckyDrawRewards,
                'luckyDrawClients'  => $luckyDrawClients,
                'luckyDrawWinners'  => $luckyDrawWinners
            ]);
        }

        abort(404, "View for event code {$eventCode} not found.");
    }

    public function updateRaffle(Request $request)
    {
        $this->service->attributes = $request->all();
        $this->service->attributes['assignee_id'] = $request->input('assignee_id');

        if ($this->service->updateRaffle()) {
            return $this->responseSuccess();
        }

        return $this->responseError('Lỗi, vui lòng kiểm tra lại', 400);
    }

    // Hàm export lucky draw
    public function exportExcelRaffleResult(LuckyDraw $lucky_draw)
    {
        $fileName = "ketquaxoso_{$lucky_draw->id}_".date('YmdHis').".xlsx";
        return Excel::download(new ResultExport($lucky_draw->id), $fileName);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LuckyDraw $lucky_draw)
    {
        $this->authorize('delete', $lucky_draw);

        $eventId = $lucky_draw->event_id;
        $this->service->update($lucky_draw->id, [
            'status' => LuckyDraw::STATUS_DELETED
        ]);

        return redirect()->route('admin.lucky_draws.index')
            ->withSuccess("Xóa quay số thành công");
    }
}
