<?php
namespace App\Services\Admin;

use App\Models\Package;
use App\Services\BaseService;

class PackageService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Package::class);
    }
}
