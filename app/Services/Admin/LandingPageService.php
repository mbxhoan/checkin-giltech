<?php
namespace App\Services\Admin;

use App\Models\LandingPage;
use App\Services\BaseService;

class LandingPageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LandingPage::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function language()
    {
        return app(LanguageService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function page_access_log()
    {
        return app(PageAccessLogService::class);
    }

    public function handleCustoms(array $customs)
    {
        if (!count($customs)) {
            return false;
        }

        foreach ($customs as $landingPageId => $customDetails) {
            $landingPage = $this->findById($landingPageId);
            if (!$landingPage) continue;
            $customs = $landingPage->customs ?? [];

            foreach ($customDetails as $key => $customDetail) {
                $customs[$key] = $customDetail;
            }

            if (count($customs)) {
                $this->update($landingPage->id, [
                    'customs' => $customs,
                ]);
            }
        }

        return true;
    }

    public function ensureLimited(int $eventId)
    {
        $list = $this->getListByAttributes([
            'event_id' => $eventId,
        ]);

        if (!empty($list) && $list->count() >= 5) {
            return false;
        }

        return true;
    }
}
