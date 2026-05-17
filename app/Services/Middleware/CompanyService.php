<?php

namespace App\Services\Middleware;

use App\Models\Company;
use App\Services\BaseService;

class CompanyService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Company::class);
    }
}
