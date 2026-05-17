<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventSettings\UpdateValueRequest;
use App\Models\Event;
use App\Services\Admin\EventSettingService;
use App\Models\EventSetting;

class EventSettingController extends Controller
{
    public function __construct(EventSettingService $service)
    {
        $this->service = $service;
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateValue(UpdateValueRequest $request, EventSetting $eventSetting)
    {
        switch ($eventSetting->input_type) {
            case ($eventSetting::INPUT_TYPE_SWITCH):
                $value = $request->value && $request->value == "true" ? true : false;
                break;

            default:
                $value = $request->only('value')['value'];
        }

        $this->service->update($eventSetting->id, [
            'value' => $value,
        ]);
        $this->service->updateCheckinEventSettingToRedis($eventSetting->event);
        return $this->responseSuccess(null, "Đã cập nhật giá trị cho {$eventSetting->name}: {$value}");
    }

    /**
     * Update the specified resource in storage.
     */
    public function syncSettings(Event $event)
    {
        if ($this->service->syncByEvent($event, true)) {
            return $this->responseSuccess([
                'html'          => view('admin.events._settings', [
                    'event'     => $event,
                    'setting'   => $this->service->init(),
                    'settings'  => $this->service->getListByAttributes([
                            'event_id' => $event->id
                        ], [], [], 0, [
                            'group'         => 'ASC',
                            'input_type'    => 'DESC',
                            // 'updated_at'    => 'DESC'
                        ]),
                    ])->render(),
                'redirectTo'    => route('admin.events.edit', $event),
            ], "Đã đồng bộ cấu hình sự kiện {$event->name}");
        }
        $this->service->updateCheckinEventSettingToRedis($event);
        return $this->responseError("Đã có lỗi xảy ra trong quá trình đồng bộ");
    }
}
