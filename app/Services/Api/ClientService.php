<?php
namespace App\Services\Api;

use App\Models\Client;
use App\Models\Event;
use App\Models\EventSetting;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Services\Middleware\EmailService as MiddlewareEmailService;
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

    public function language()
    {
        return app(LanguageService::class);
    }

    public function user()
    {
        return app(UserService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function middleware_card()
    {
        return app(MiddlewareCardService::class);
    }

    public function register(Event $event, Client $client)
    {
        /* đăng ký checkin luôn */
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

        /* đăng ký tạo thiệp */
        $cardId = $this->attributes['card_id'] ?? null;

        /* customize */
        /* hidec-2025 */
        if ($event->code == "hidec-2025") {
            $cardId = 23;
        }

        if ($cardId) {
            $this->middleware_card()->generateCardNow($cardId, $client->id);

            /* customize */
            /* hidec-2025 */
            // if ($event->code == "hidec-2025") {

            // }

            // if ($result['status']) {
            //     $client->refresh();
            //     $file = $client->document_pdf;
            //     $filePath = "public/{$file}";

            //     if ($file && Storage::exists($filePath)) {
            //         return response()->file(storage_path("app/{$filePath}"));
            //     } else {
            //         abort(404);
            //         return response()->json(['error' => 'Không tìm thấy file. Vui lòng thử lại sau...'], 404);
            //     }
            // } else {
            //     abort(404, $result['msg']);
            //     return response()->json([
            //         'status'            => 'error',
            //         'status_code'       => 400,
            //         'message'           => $result['msg'],
            //     ]);
            // }
        }

        /* đăng ký gửi email */
        if ($event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? null) {
            $client->refresh();
            $campaignId = $this->attributes['campaign_id'] ?? null;

            if ($campaignId) {
                $this->middleware_email()->sendEmailGlobalByClient($client, $campaignId);
            }
        }

        return [
            'status'    => true,
            'msg'       => __('responses.create.success'),
        ];
    }
}
