<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\Jobs\SendEmailJob;
use App\Models\Campaign;
use App\Services\BaseService;
use App\Models\CampaignDetail;
use App\Models\Client;
use App\Models\Email;
use Illuminate\Support\Facades\Log;

class CampaignDetailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(CampaignDetail::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function email_template()
    {
        return app(EmailTemplateService::class);
    }

    public function email()
    {
        return app(EmailService::class);
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

    public function cloneClientByType(Campaign $campaign)
    {
        $filters = [
            'event_id'  => $campaign->event_id,
            'status'    => [
                Client::STATUS_ACTIVE,
                Client::STATUS_NEW,
            ],
            'type'      => $campaign->type,
        ];

        $clients = $this->client()->getListByAttributes(array_filter($filters), [
            'email'     => null
        ]);

        $this->campaign()->update($campaign->id, [
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        if ($clients->count() == 0) {
            return [
                'status'        => false,
                'message'       => "Không tìm thấy khách hàng nào",
            ];
        }

        $this->resetListByCampaign($campaign);

        foreach ($clients as $client) {
            $attributes = [
                'id'            => null,
                'campaign_id'   => $campaign->id,
                'name'          => $client->name,
                'qrcode'        => $client->qrcode,
                'img_qrcode'    => route('clients.view-qrcode-by-id', [
                    'id'        => $client->id
                ]),
                'document_pdf'  => route('clients.view-document-pdf', [
                    'clientId'  => $client->id,
                ]),
                'gender'        => $client->gender,
                'email'         => $client->email,
                'email_form'    => Helper::checkEmailForm($client->email),
                'phone'         => $client->phone,
                'custom_fields' => json_encode($client->getCustomFieldValues(false)),
                'send_email'    => 1,
                'send_zalo'     => 0,
                'send_sms'      => 0,
                'status'        => CampaignDetail::STATUS_ACTIVE,
            ];

            try {
                $this->create($attributes);
            } catch (\Exception $e) {
                Log::info("Lỗi cập nhật Campaign Detail");
                Log::alert($e);
                return [
                    'status'    => false,
                    'message'   => "Lỗi cập nhật Campaign Detail {$client->qrcode}",
                ];
            }
        }

        return [
            'status'    => true,
            'message'   => "Đã đồng bộ {$clients->count()} khách hàng",
        ];
    }

    public function resetListByCampaign(Campaign $campaign)
    {
        foreach ($campaign->campaign_details as $campaignDetail) {
            $campaignDetail->delete();
        }

        return true;
    }

    public function setupEmails(Campaign $campaign)
    {
        $attr = [];
        $param = [];
        $countEmail = 0;

        if (!empty($campaign->campaign_details) && $campaign->campaign_details->count() > 0) {
            $this->email()->closeAllEmailByCampaign($campaign);

            if ($campaign->status == Campaign::STATUS_NEW) {
                $this->update($campaign->id, [
                    'status' => Campaign::STATUS_ACTIVE,
                ]);
            }

            foreach ($campaign->campaign_details as $campaignDetail) {
                if (!$campaignDetail->email) {
                    continue;
                }

                if ($campaignDetail->email_form) {
                    $param = [
                        'name'          => $campaignDetail->name,
                        'email'         => $campaignDetail->email,
                        'phone'         => $campaignDetail->phone,
                        'qrcode'        => $campaignDetail->qrcode,
                        'img_qrcode'    => $campaignDetail->img_qrcode,
                        'document_pdf'  => $campaignDetail->document_pdf,
                        'cc'            => implode(', ', json_decode($campaign->cc, true)),
                        'bcc'           => implode(', ', json_decode($campaign->bcc, true)),
                    ];

                    $attr = [
                        'id'            => null,
                        'campaign_id'   => $campaign->id,
                        'subject'       => $campaign->subject,
                        'from_name'     => $campaign->from_name,
                        'from_email'    => $campaign->from_email,
                        'template_id'   => $campaign->template_id,
                        'is_online'     => 1, // $campaign->is_online
                        'param'         => json_encode(array_merge($param, (array)json_decode($campaignDetail->custom_fields))),
                        'email'         => $campaignDetail->email,
                        'qrcode'        => $campaignDetail->qrcode,
                        'to_name'       => $campaignDetail->name,
                        'to_email'      => $campaignDetail->email,
                        'status'        => Email::STATUS_WAITING,
                    ];

                    if ($this->email()->create($attr)) {
                        $countEmail++;
                    }
                }
            }

            $this->update($campaign->id, [
                'total_emails' => (int)$countEmail,
                'status'       => Campaign::STATUS_SENDING,
            ]);

            /* chưa dám */
            if ($this->sendEmailList($campaign)) {
                $this->update($campaign->id, [
                    'status' => Campaign::STATUS_COMPLETED,
                ]);
            }

            /* Call Job Send Mail */
            // $objJob = new SendEmailJob();
            // $objJob->timeout = 600;
            // $sendMail = $objJob->delay(Carbon::now()->addSeconds(5));
            // dispatch($sendMail);

            // Artisan::call("send:mail", ['campaignId' => $campaignId]);
            return true;
        }

        return null;
    }

    public function sendEmailList(Campaign $campaign)
    {
        $emails = $this->email()->getListByAttributes([
            'campaign_id' => $campaign->id,
            'status'      => Email::STATUS_WAITING,
        ], [], [], 0, [
            'id' => 'asc'
        ]);

        if ($emails->count()) {
            foreach ($emails as $email) {
                // dispatch(new SendEmailJob($campaign->id, $email->id));
                $this->email()->sendMailByJob($email);
            }
        }

        return true;
    }
}
