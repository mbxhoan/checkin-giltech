<?php
namespace App\Services\Admin;

use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Services\BaseService;

class CustomFieldTemplateService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(CustomFieldTemplate::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function initByEvent(Event $event)
    {
        $customFieldTemplates = $this->getListByAttributes([
            'event_id' => $event->id
        ]);

        if ($customFieldTemplates && $customFieldTemplates->count()) {

        } else {
            $defaultCustomFieldTemplates = $this->init()->getDefaultCustomFieldTemplate();
            $count = 1;

            foreach ($defaultCustomFieldTemplates as $name => $templateAttr) {
                $this->create([
                    'is_default'            => true,
                    'event_id'              => $event->id,
                    'required'              => $templateAttr['required'],
                    'unique'                => $templateAttr['unique'],
                    'is_lp'                 => $templateAttr['is_lp'],
                    'is_checkin_mobile'     => $templateAttr['is_checkin_mobile'],
                    'is_checkin_desktop'    => $templateAttr['is_checkin_desktop'],
                    'name'                  => $name,
                    'order'                 => $count++,
                    'description'           => $templateAttr['desc'],
                    'type'                  => $templateAttr['type'],
                ]);
            }
        }

        return true;
    }

    public function updateCheckinsField(int $id, array $checkins)
    {
        $model = $this->findById($id);

        if (!count($checkins) || !$model) {
            return false;
        }

        $currentCheckins = $model->checkins ?? [];

        foreach ($checkins as $screen => $configs) {
            /* set for boolean columns */
            foreach ([
                'bold',
                'italic',
                'underline',
                'bg',
            ] as $field) {
                if (isset($configs[$field])) {
                    $configs[$field] = (($configs[$field] == "true" || $configs[$field] == "1") ? 1 : 0);
                } else {
                    $configs[$field] = 0;
                }
            }

            /* loop the request checkins attributes */
            foreach ($configs as $field => $value) {
                if (in_array($field, [
                    'bold'
                ])) {
                    if (isset($value)) {
                        $value = (($value == "true" || $value == "1") ? 1 : 0);
                    } else {
                        $value = 0;
                    }
                }

                $currentCheckins[$screen][$field] = $value;
            }

            if (count($currentCheckins)) {
                $this->update($model->id, [
                    'checkins' => $currentCheckins,
                ]);
            }
        }

        return true;
    }
}
