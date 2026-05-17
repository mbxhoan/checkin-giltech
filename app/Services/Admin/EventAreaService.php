<?php
namespace App\Services\Admin;

use App\Models\EventArea;
use App\Services\BaseService;

class EventAreaService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EventArea::class);
    }

    public function event()
    {
        return app(EventService::class);
    }
}
