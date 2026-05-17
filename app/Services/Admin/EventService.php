<?php
namespace App\Services\Admin;

use App\Models\Event;
use App\Services\BaseService;
use App\Services\Middleware\ClientService as MiddlewareClientService;

class EventService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Event::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function province()
    {
        return app(ProvinceService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function event_file()
    {
        return app(EventFileService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function card()
    {
        return app(CardService::class);
    }

    public function label()
    {
        return app(LabelService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function ensureLimited(int $companyId, string $field)
    {
        $company = $this->company()->findById($companyId);

        if (isset($company->$field) && $company->$field > 0) {
            $list = $this->getListByAttributes([
                'company_id' => $companyId,
            ]);

            if (!empty($list) && $list->count() >= $company->$field) {
                return false;
            }
        }

        return true;
    }

    public function removeSetting(Event $event, string $group, string $name)
    {
        $eventSetting = $event->getEventSetting($name, $group);

        if ($eventSetting) {
            return $this->event_setting()->delete($eventSetting->id);
        }

        return null;
    }

    public function getEventList(array $attributes = [])
    {
        $eventFilters = $this->getEventFilters();

        if (!isset($attributes['status'])) {
            $attributes['status'] = [
                Event::STATUS_ACTIVE,
                Event::STATUS_NEW,
            ];
        }

        $events = $this->getListByAttributes(array_merge($attributes, $eventFilters));
        return $events;
    }

    public function getEventFilters()
    {
        if (!auth()->user()->isSysAdmin()) {
            $eventFilters = [
                'company_id' => auth()->user()->company->id,
            ];
        }

        return $eventFilters ?? [];
    }
}
