<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Language as LanguageResource;
use App\Models\Language;

class LandingPage extends JsonResource
{
    protected $lang;

    public function __construct($resource, string $lang)
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $languageIds = array_values($this->languages ? json_decode($this->languages, true) : []);
        $registerSendMail = $this->event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? false;
        $languages = Language::whereIn('id', $languageIds)
            ->where('status', '!=', Language::STATUS_DELETED)
            ->get();
        $languages = LanguageResource::collection($languages);

        if ($registerSendMail) {
            $campaign = $this->landingPageCampaigns->where('lang', $this->lang)
                ->first()
                ->campaign ?? null;
        }

        $card = $this->landingPageCards->where('lang', $this->lang)
            ->first()
            ->card ?? null;



        return [
            'id'                => $this->id,
            'template_id'       => $this->template_id,
            'open_form'         => $this->event->getEventSetting("ENABLE_FORM", null)->value ?? false,
            'open_captcha'      => $this->event->getEventSetting("ENABLE_CAPTCHA", null)->value ?? false,
            'open_name_card_ocr' => $this->event->getEventSetting("OPEN_NAME_CARD_OCR", null)->value ?? false,
            'open_scan_cccd'    => $this->event->getEventSetting("OPEN_SCAN_CCCD", null)->value ?? false,
            'event_id'          => $this->event_id,
            'event_code'        => $this->event->code,
            'event_name'        => $this->event->name,
            'event'             => new EventResource($this->event),
            'show_lang'         => $this->show_language_selection ?? false,
            'slug'              => $this->slug,
            'tracking'          => $this->tracking,
            'customs'           => $this->customs,
            'orders'            => $this->orders,
            'align'             => $this->align,
            'form_width'        => $this->form_width,
            // 'languages'         => json_decode($this->languages, true),
            'languages'         => $languages,
            'banner'            => $this->banner_id ? $this->banner->getUrl() : null,
            'header'            => $this->header_id ? $this->header->getUrl() : null,
            'footer'            => $this->footer_id ? $this->footer->getUrl() : null,
            'bg_desktop'        => $this->bg_desktop_id ? $this->bg_desktop->getUrl() : null,
            'bg_tablet'         => $this->bg_tablet_id ? $this->bg_tablet->getUrl() : null,
            'bg_mobile'         => $this->bg_mobile_id ? $this->bg_mobile->getUrl() : null,
            'contact_name'      => $this->contact_name,
            'contact_phone'     => $this->contact_phone,
            'contact_email'     => $this->contact_email,
            'contact_address'   => $this->contact_address,
            'status'            => $this->status,
            'campaign_id'       => !empty($campaign) ? $campaign->id : null,
            'card_id'           => !empty($card) ? $card->id : null,
            'css'               => $this->generateCssFromCustoms(),
        ];
    }
}
