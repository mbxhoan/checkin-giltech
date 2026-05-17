<?php

namespace App\Services\Admin;

use App\Jobs\GenerateLanguageDefineJob;
use App\Models\LanguageDefine;
use App\Services\BaseService;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class LanguageDefineService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LanguageDefine::class);
    }

    public function language()
    {
        return app(LanguageService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function generateLang(?string $eventCode = null)
    {
        /* Call Job Generating lang */
        $objJob = new GenerateLanguageDefineJob($eventCode);
        $objJob->timeout = 600;
        $importExcelToDb = $objJob->delay(Carbon::now()->addSeconds(1));
        dispatch($importExcelToDb);
        return true;
    }

    public function generateLanguageDefinesByCmd($eventCode)
    {
        Artisan::call("lang:generate {$eventCode}");
    }
}
