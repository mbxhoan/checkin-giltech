<?php
namespace App\Services\Admin;

use App\Models\Event;
use App\Services\Middleware\LabelService as MiddlewareLabelService;
use App\Services\BaseService;
use App\Models\Label;

class LabelService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Label::class);
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

    public function label_detail()
    {
        return app(LabelDetailService::class);
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

    public function middleware_label()
    {
        return app(MiddlewareLabelService::class);
    }

    public function setUnDefaultByEvent(Event $event)
    {
        $labels = $this->getListByAttributes([
            'event_id'      => $event->id,
            'is_default'    => true,
        ]);

        foreach ($labels as $label) {
            $this->update($label->id, [
                'is_default' => false,
            ]);
        }

        return true;
    }
}
