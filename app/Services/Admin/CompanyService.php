<?php

namespace App\Services\Admin;

use App\Models\Company;
use App\Services\BaseService;

class CompanyService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Company::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function language()
    {
        return app(LanguageService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function email_template()
    {
        return app(EmailTemplateService::class);
    }

    public function email_sender()
    {
        return app(EmailSenderService::class);
    }

    public function handleSettings(?Company $company, array $settings)
    {
        if (!count($settings)) return [];
        $defaultSettings = $this->getConfigEventSettings();
        $newSettings = [];

        foreach ($settings as $group => $settingMores) {
            foreach ($settingMores as $name => $settingAttr) {
                if (isset($settingAttr['show']) && $settingAttr['show']) {
                    $newSettings[$group][$name] = $defaultSettings[$group][$name];
                    $newSettings[$group][$name]['description'] = $settingAttr['description'];
                } else {
                    if ($company) {
                        $this->removeSettingAllEvents($company, $group, $name);
                    }
                }
            }
        }

        // foreach ($defaultSettings as $group => $settingMores) {
        //     foreach ($settingMores as $name => $settingAttr) {
        //         if (isset($settings[$group][$name]['show']) && $settings[$group][$name]['show']) {
        //             $newSettings[$group][$name] = $defaultSettings[$group][$name];
        //             $newSettings[$group][$name]['description'] = $settingAttr['description'];
        //         }
        //     }
        // }

        return $newSettings;
    }

    public function removeSettingAllEvents(Company $company, string $group, string $name)
    {
        $events = $company->events;

        foreach ($events as $event) {
            $this->event()->removeSetting($event, $group, $name);
        }

        return true;
    }
}
