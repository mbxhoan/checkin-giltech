<?php

namespace App\Services\Web;

use Illuminate\Support\Facades\App;
use App\Models\Language;
use App\Services\BaseService;

class LanguageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Language::class);
    }

    public function changeLanguage($session, $lang, $setLocale = false)
    {
        $sessionId = $session->getId();
        $session->put("{$sessionId}.language", $lang);

        if ($setLocale) {
            App::setLocale($lang);
        }

        return $session->get("{$sessionId}.language");
    }
}
