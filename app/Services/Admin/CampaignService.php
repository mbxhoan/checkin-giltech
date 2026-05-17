<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\Services\BaseService;
use App\Models\Campaign;

class CampaignService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Campaign::class);
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

    public function campaign_detail()
    {
        return app(CampaignDetailService::class);
    }

    public function email_template()
    {
        return app(EmailTemplateService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function email_sender()
    {
        return app(EmailSenderService::class);
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

    public function postmark()
    {
        return app(PostmarkService::class);
    }

    public function middleware_client()
    {
        // return app(MiddlewareClientService::class);
    }

    public function convertEmailStringToArray($emailString)
    {
        $emailArray = explode(',', $emailString);
        $emailArray = array_map('trim', $emailArray);

        if (count($emailArray)) {
            foreach ($emailArray as $key => $email) {
                if (!Helper::checkEmailForm($email)) {
                    unset($emailArray[$key]);
                }
            }
        }

        return $emailArray;
    }
}
