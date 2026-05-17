<?php
namespace App\Services\Middleware;

use App\Models\Event;
use App\Services\BaseService;

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

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }
}
