<?php

namespace App\Services\Web;

use App\Models\Campaign;
use App\Services\BaseService;

class CampaignService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Campaign::class);
    }
}
