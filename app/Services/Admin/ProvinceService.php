<?php

namespace App\Services\Admin;

use App\Models\Province;
use App\Services\BaseService;

class ProvinceService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Province::class);
    }
}
