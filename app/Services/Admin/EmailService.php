<?php
namespace App\Services\Admin;

use App\Jobs\SendEmailJob;
use App\Models\Campaign;
use App\Services\BaseService;
use App\Models\Email;
use App\Services\Middleware\EmailService as MiddlewareEmailService;

class EmailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Email::class);
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

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function closeAllEmailByCampaign(Campaign $campaign)
    {
        $emails = $this->getListByAttributes([
            'campaign_id' => $campaign->id,
        ]);

        foreach ($emails as $email) {
            $this->update($email->id, [
                'status' => Email::STATUS_CLOSED,
            ]);
        }

        return true;
    }

    public function sendMailByJob($email)
    {
        $this->middleware_email()->sendMailByJob($email);
        return $email->refresh();
    }

    public function sendMailNow($email)
    {
        return $this->middleware_email()->sendMailNow($email);
    }

    public function setEmailWaiting(Email $email, string $send = "job")
    {
        return $this->middleware_email()->setEmailWaiting($email, $send);
    }
}
