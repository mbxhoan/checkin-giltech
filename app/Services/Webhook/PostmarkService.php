<?php

namespace App\Services\Webhook;

use App\Models\WebhookPostmark;
use App\Services\BaseService;

class PostmarkService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(WebhookPostmark::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }
}
