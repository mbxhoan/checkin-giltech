<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use App\Services\Web\LandingPageService;

class LandingPageController extends Controller
{
    public function __construct(LandingPageService $service)
    {
        $this->service = $service;
    }

    public function register(Request $request, $slug)
    {
        $domain = env("REGISER_DOMAIN");

        if ($domain) {
            if (env("APP_ENV") === 'local') {
                $domain = "http://{$domain}";
            } else {
                $domain = "https://{$domain}";
            }

            return redirect()->away("{$domain}/{$slug}");
        }


        $landingPage = $this->service->findByAttributes([
            'slug'      => trim($slug),
            'status'    => LandingPage::STATUS_ACTIVE,
        ]);

        if ($landingPage) {
            if ($landingPage->checkIfLandingPageIsValid()) {
                $lang = $request->lang ?? app()->getLocale();
                $this->service->setLanguage($landingPage, $request->session(), $lang);
                $registerSendMail = $landingPage->event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? false;

                if ($registerSendMail) {
                    $campaign = $landingPage->landingPageCampaigns->where('lang', $lang)
                        ->first()
                        ->campaign ?? null;
                }

                return view('web.landing_pages.register', [
                    'model'                 => $landingPage,
                    'event'                 => $landingPage->event,
                    'client'                => $this->service->client()->init(),
                    'cfTemplate'            => $this->service->custom_field_template()->init(),
                    'customFieldTemplates'  => $landingPage->event->getCustomFieldTemplates(true, true),
                    'campaign'              => $campaign ?? null,
                    // 'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                    //     'event_id'      => $event->id,
                    //     'is_lp'         => true,
                    // ], [], [], 0, [
                    //     'order' => 'DESC',
                    // ]),
                ]);
            }

        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy trang');
    }

    public function success(Request $request, string $slug, string $qrcode)
    {
        $landingPage = $this->service->findByAttributes([
            'slug'      => trim($slug),
            'status'    => LandingPage::STATUS_ACTIVE,
        ]);

        if ($landingPage) {
            if ($landingPage->checkIfLandingPageIsValid()) {
                $lang = $request->lang ?? app()->getLocale();
                $this->service->setLanguage($landingPage, $request->session(), $lang);

                $client = $this->service->client()->findByAttributes([
                    'event_id'    => $landingPage->event_id,
                    'qrcode'      => $qrcode,
                ]);

                if ($client) {
                    return view('web.landing_pages.success', [
                        'model'                 => $landingPage,
                        'event'                 => $landingPage->event,
                        'client'                => $client,
                        'cfTemplate'            => $this->service->custom_field_template()->init(),
                        'customFieldTemplates'  => $landingPage->event->getCustomFieldTemplates(true, true),
                    ]);
                }
            }
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy trang/khách hàng');
    }
}
