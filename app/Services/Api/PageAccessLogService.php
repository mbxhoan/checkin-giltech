<?php
namespace App\Services\Api;

use App\Models\PageAccessLog;
use App\Services\BaseService;

class PageAccessLogService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(PageAccessLog::class);
    }
}
