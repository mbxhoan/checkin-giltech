<?php
namespace App\Services\Web;

use App\Services\BaseService;

class HomeService extends BaseService
{
    public function __construct()
    {

    }

    public function language()
    {
        return app(LanguageService::class);
    }
}
