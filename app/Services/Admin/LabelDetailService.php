<?php
namespace App\Services\Admin;

use App\Services\BaseService;
use App\Models\LabelDetail;

class LabelDetailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(LabelDetail::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }
}
