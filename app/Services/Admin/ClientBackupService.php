<?php

namespace App\Services\Admin;

use App\Models\ClientBackup;
use App\Services\BaseService;

class ClientBackupService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(ClientBackup::class);
    }

    public function event()
    {
        return app(EventService::class);
    }
}
