<?php

namespace App\Services\Admin;

use App\Models\Language;
use App\Services\BaseService;

class LanguageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Language::class);
    }
}
