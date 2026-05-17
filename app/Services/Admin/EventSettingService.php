<?php
namespace App\Services\Admin;

use App\Models\Event;
use App\Models\EventSetting;
use App\Services\BaseService;

class EventSettingService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EventSetting::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function syncByEvent(Event $event, $update = false)
    {
        // $eventDefaultSettings = $this->getConfigEventSettings();
        $eventDefaultSettings = $event->company->settings ?? [];

        if (!is_array($eventDefaultSettings)) {
            $eventDefaultSettings = json_decode($event->company->settings, true) ?? [];
        }

        /* disabled all settings */
        foreach ($event->getEventSettings() as $setting) {
            $this->update($setting->id, [
                'status' => EventSetting::STATUS_INACTIVE
            ]);
        }

        /* init setting */
        foreach ($eventDefaultSettings as $group => $settings) {
            foreach ($settings as $setting) {
                $settingAttr = [];
                $parentSetting = null;

                /* find existed setting */
                $existSetting = $this->findByAttributes([
                    'event_id'  => $event->id,
                    'name'      => $setting['name'],
                    'group'     => $group,
                ]);

                /* parent setting */
                if (isset($setting['parent'])) {
                    $parentKey = $setting['parent'];
                    $parentSetting = $this->findByAttributes([
                        'event_id'  => $event->id,
                        'name'      => $settings[$parentKey]['name'],
                        'group'     => $group,
                    ]);
                }

                $settingAttr = [
                    'parent_id'     => $parentSetting->id ?? null,
                    'event_id'      => $event->id,
                    'name'          => $setting['name'],
                    'description'   => $setting['description'],
                    'value'         => is_array($setting['value']) ? json_encode($setting['value']) : $setting['value'],
                    'options'       => $setting['options'] ?? null,
                    'group'         => $group,
                    'input_type'    => $setting['input_type'],
                    'status'        => EventSetting::STATUS_ACTIVE,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ];

                if (!empty($existSetting)) {
                    if ($update) {
                        $this->update($existSetting->id, $settingAttr);
                    }
                } else {
                    $this->create($settingAttr);
                }
            }
        }

        return true;
    }

    public function updateCheckinEventSettingToRedis(Event $event)
    {
        foreach ([
            EventSetting::GROUP_MOBILE,
            EventSetting::GROUP_DESKTOP,
        ] as $group) {
            $eventSettings = $event->getEventSettings($group);
            $this->updateRedis("event_settings:{$group}", $event->code, json_encode($eventSettings), config("app.times.minutes.30"));
        }

        return true;
    }
}
