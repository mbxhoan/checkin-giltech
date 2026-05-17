<?php
namespace App\Services\Api;

use App\Models\LandingPage;
use App\Services\BaseService;

class LandingPageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LandingPage::class);
    }

    public function home()
    {
        return app(HomeService::class);
    }

    public function event()
    {
        return app(EventService::class);
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
