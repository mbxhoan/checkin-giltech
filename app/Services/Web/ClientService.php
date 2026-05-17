<?php
namespace App\Services\Web;

use App\Models\Client;
use App\Models\EventSetting;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Services\Middleware\EmailService as MiddlewareEmailService;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use App\Services\Middleware\CardService as MiddlewareCardService;

class ClientService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Client::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function card()
    {
        return app(CardService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function middleware_card()
    {
        return app(MiddlewareCardService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function register(Client $client)
    {
        $event = $client->event;

        if ($event->getEventSetting("REGISTER_CHECKIN", null)->value ?? null) {
            $checkin = new MiddlewareCheckinService(
                $this->attributes['event_code'],
                $this->attributes['qrcode'],
                now()->format('Y-m-d H:i:s'),
            );

            $checkin->attributes = [
                'user_group'    => $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP,
            ];

            $checkin->checkin();
        }

        if ($event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? null) {
            $campaignId = $this->attributes['campaign_id'] ?? null;

            if ($campaignId) {
                $this->middleware_email()->sendEmailGlobalByClient($client, $campaignId);
            }

            // $templateId = 39930830;
            // $variables = [
            //     "subject"               => "{$client->event->name}: Xác nhận đăng ký thành công",
            //     "name"                  => $client->name,
            //     "event_name"            => $client->event->name,
            //     "qrcode"                => $client->qrcode,
            //     "img_qrcode"            => route('clients.view-qrcode-by-id', [
            //         'id'             => $client->id
            //     ]),
            // ];

            // $this->middleware_email()->sendMailTestCurl($client->email, $templateId, $variables);
            // var_dump();
        }

        return [
            'status'    => true,
            'msg'       => __('responses.create.success'),
        ];
    }
}
