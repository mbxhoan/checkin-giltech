<?php
namespace App\Services\Web;

use App\Models\Client;
use App\Models\EventSetting;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Services\Middleware\EmailService as MiddlewareEmailService;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use App\Services\Middleware\EmailTemplateService;

class CampaignDetailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Client::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function middleware_email_template()
    {
        return app(EmailTemplateService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }
}
