<?php
namespace App\Services\Admin;

use App\Models\PageAccessLog;
use App\Services\BaseService;

class PageAccessLogService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(PageAccessLog::class);
    }
}
