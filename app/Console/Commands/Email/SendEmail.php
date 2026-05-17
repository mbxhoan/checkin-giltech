<?php

namespace App\Console\Commands\Email;

use App\Helpers\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Email;
use App\Traits\SendMail;
use App\Mail\SendMailBySendgrid;
use App\Services\Middleware\EmailService;
use Exception;
use Mail;

class SendEmail extends Command
{
    use SendMail;

    protected $modelCampaign;
    protected $limit;
    protected $holdEachMail;
    protected $campaignId;
    protected $emailId;
    protected $customLimit;
    protected $options;
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mail {--campaignId=} {--emailId=} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email to clients';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function __construct(EmailService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->options = (object)$this->options();
        $this->campaignId = is_numeric($this->options->campaignId) ? $this->options->campaignId : 0;
        $this->emailId = is_numeric($this->options->emailId) ? $this->options->emailId : 0;
        $this->customLimit = is_numeric($this->options->limit) ? $this->options->limit : 15;

        if ($this->emailId) {
            $email = $this->service->findById($this->emailId);

            if ($email && $email->status == Email::STATUS_WAITING) {
                $this->holdEachMail = $email->campaign->hold_time ?? 1;
                sleep($this->holdEachMail);
                $this->sendSingleEmail($email);
            }
        } else {
            if ($this->campaignId) {
                $campaign = $this->service->findById($this->campaignId);

                if ($campaign) {
                    $this->limit = $campaign->limitation_per_time ?? ($this->customLimit ?? 15);
                    $this->holdEachMail = $campaign->hold_time ?? 5;

                    $emails = $this->service->getListByAttributes([
                        'campaign_id'   => $campaign->id,
                        'status'        => Email::STATUS_WAITING,
                        'message_id'    => null,
                    ], [], [], 0, [
                        'id'            => 'asc'
                    ], false, $this->limit ?? 5);

                    if ($emails->count()) {
                        $this->sendGroupMail($emails, $this->holdEachMail);
                    } else {
                        $this->error("Tìm thấy 0 email gửi trên campaign {$this->campaignId}");
                    }
                }
            } else {
                $emails = $this->service->getListByAttributes([
                    'status'        => Email::STATUS_WAITING,
                    'message_id'    => null,
                ], [], [], 0, [
                    'id'            => 'asc'
                ], false, $this->customLimit);

                if ($emails->count()) {
                    $this->sendGroupMail($emails, $this->holdEachMail);
                } else {
                    $this->error("Tìm thấy 0 email gửi");
                }
            }
        }

        return Command::SUCCESS;
    }

    /* SEND BY SENDGRID */
    private function sendGroupMail($emails)
    {
        $this->line("LIMIT: {$this->limit}");
        foreach ($emails as $email) {
            $email->update([
                'message_id'    => 1,
            ]);
        }

        foreach ($emails as $email) {
            $this->sendSingleEmail($email);

            if (!$this->emailId) {
                sleep($this->holdEachMail);
            }
        }

        return true;
    }

    private function sendSingleEmail($email)
    {
        if ($email->status == Email::STATUS_SENT) {
            $this->error("Email {$email->to_email} has been sent");
            return;
        }

        $this->line("Preparing to send: {$email->to_email}. *** WAIT FOR {$this->holdEachMail} seconds ***");

        /* check to_email column */
        if (!$email->to_email) {
            $email->update([
                'error_log' => "No email found...",
                'status'    => Email::STATUS_NEW,
            ]);

            $this->error("No email found...");
            return;
        }

        /* validate mail form */
        if (!Email::checkEmailForm($email->to_email)) {
            $email->update([
                'error_log' => "Email: {$email->to_email} NOT IN correct format!",
                'status'    => Email::STATUS_NEW,
            ]);

            $this->error("Email: {$email->to_email} NOT IN correct format!");
            return;
        }

        /* validate limit send mail */
        $campaign = $email->campaign;
        $event = $campaign->event;
        $company = $event->company;
        if (!empty($company->limited_emails) && $company->limited_emails > 0) {
            $sentEmailCount = Email::whereNotNull('sent_at')
                ->whereHas('campaign.event', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })
                ->count();

            if ($sentEmailCount >= $company->limited_emails) {
                $email->update([
                    'error_log' => "Số email gửi đi ({$sentEmailCount}) đã vượt quá số lượng cho phép ({$company->limited_emails})",
                    'status'    => Email::STATUS_NEW,
                ]);

                return;
            }
        }

        if ($email->is_online) {
            $emailSend = $this->sendSingleEmailByPostmark($email);
        } else {
            $emailSend = $this->sendSingleOfflineByPostmark($email);
        }

        if ((int)$emailSend == 202) {
            $this->info("Sent to: {$email->to_email}");

            $email->update([
                'sent_at'   => date('Y-m-d H:i:s'),
                'status'    => Email::STATUS_SENT,
            ]);
        } else if ($emailSend == 1) {
            $this->info("Sent to: {$email->to_email}");

            $email->update([
                'sent_at'   => date('Y-m-d H:i:s'),
                'status'    => Email::STATUS_SENT,
            ]);
        } else {
            $this->error("PASSED: {$email->to_email}");
            $email->update([
                // 'error_log' => "PASSED: {$email->to_email}",
                'status'    => Email::STATUS_NEW,
            ]);
        }

        if (!$this->holdEachMail) {
            $this->holdEachMail = $email->campaign->hold_time ?? 5;
        }

        return $emailSend;
    }
}
