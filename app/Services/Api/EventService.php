<?php
namespace App\Services\Api;

use App\Models\Event;
use App\Services\BaseService;

class EventService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Event::class);
    }

    public function home()
    {
        return app(HomeService::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function language()
    {
        return app(LanguageService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }
}
