<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\HttpClient\HttpClient;
use App\Services\BaseService;
use App\Models\EmailTemplate;
use App\Services\Middleware\EmailTemplateService as MiddlewareEmailTemplateService;
use Illuminate\Support\Facades\Log;

class EmailTemplateService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EmailTemplate::class);
        $this->httpClient = new HttpClient(env("POSTMARK_API_URL"), [
            "Accept"                    => "application/json",
            "X-Postmark-Server-Token"   => env("POSTMARK_TOKEN"),
        ]);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function middleware_email_template()
    {
        return app(MiddlewareEmailTemplateService::class);
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

    public function middleware_client()
    {
        // return app(MiddlewareClientService::class);
    }

    public function getPostmarkTemplates(bool $resync = false)
    {
        return $this->middleware_email_template()->getPostmarkTemplates($resync);
    }
}
