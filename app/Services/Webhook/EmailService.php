<?php
namespace App\Services\Webhook;

use App\Services\BaseService;
use App\Models\Email;

class EmailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Email::class);
    }
}
