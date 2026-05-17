<?php
namespace App\Services\Scan;

use App\Models\CustomFieldTemplate;
use App\Services\BaseService;

class CustomFieldTemplateService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(CustomFieldTemplate::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }
}
