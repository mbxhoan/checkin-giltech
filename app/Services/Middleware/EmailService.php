<?php
namespace App\Services\Middleware;

use App\Helpers\Helper;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\BaseService;
use App\Models\Email;
use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class EmailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Email::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function sendMailTestCurl(string $email, int $templateId, array $variables)
    {
        $response = Http::withHeaders([
            'Accept'                    => 'application/json',
            'Content-Type'              => 'application/json',
            'X-Postmark-Server-Token'   => env('POSTMARK_SYS_TOKEN'),
        ])->post('https://api.postmarkapp.com/email/withTemplate', [
            'From'                      => env('FROM_MAIL'),
            'To'                        => $email,

            "TemplateId"                => $templateId, // DELFI - Register Successfully
            "TemplateModel"             => $variables,

            // 'Subject'                   => 'Welcome!',
            // 'HtmlBody'                  => '<strong>Hello</strong> dear Delfi checkin user.',

            'MessageStream'             => 'delfi',

            // 'MessageStream'             => 'delfi-stream',
        ]);

        if ($response->successful()) {
            return [
                'error' => null,
                'msg'   => "Email sent successfully."
            ];
        }

        return [
            'error'     => 424,
            'msg'       => "Failed to send email: " . $response->body(),
        ];
    }

    public function sendWithPostmarkTemplate(int $templateId)
    {
        try {
            $datas = [
                "From"          => $this->attributes['from_mail'],
                "To"            => $this->attributes['to_mail'],
                "TemplateId"    => $templateId,
                "TemplateModel" => $this->attributes['fields'],
                "InlineCss"     => true,
                "Cc"            => $this->attributes['cc'],
                "Bcc"           => $this->attributes['bcc'],
                "Tag"           => "TEST-{$templateId}",
                "ReplyTo"       => $this->attributes['from_mail'],
                "TrackOpens"    => true,
                "TrackLinks"    => "None",
                "Metadata"      => [
                    "color"     => "blue",
                    "client-id" => "12345"
                ],
                "MessageStream" => "outbound"
            ];

            $result = $this->httpClient->post("email/withTemplate", $datas);

            if ($result && (isset($result['Message']) && $result['Message'] == "OK")) {
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Send Test with Template: {$e}");
        }

        return [];
    }

    public function setEmailWaiting(Email $email, string $send = "job")
    {
        $savedEmail = $email->replicate();
        $savedEmail->status = Email::STATUS_CLOSED;
        $savedEmail->save();

        $this->update($email->id, [
            'message_id'    => null,
            'status'        => Email::STATUS_WAITING,
        ]);

        switch ($send) {
            case 'job':
                $this->sendMailByJob($email);
                break;
            case 'now':
                $this->sendMailNow($email);
                break;
            default:
        }

        return $email->refresh();
    }

    public function sendEmailGlobalByClient(Client $client, int $campaignId)
    {
        $campaign = $this->campaign()->findByAttributes([
            'id' => $campaignId,
        ]);

        if (empty($campaign) || (!$client->email || !Helper::checkEmailForm($client->email))) return null;

        $param = [
            'id'            => $client->id,
            'name'          => $client->name,
            'qrcode'        => $client->qrcode,
            'email'         => $client->email,
            'phone'         => $client->phone,
            'type'          => $client->type,
            'img_qrcode'    => route('clients.view-qrcode-by-id', [
                'id'        => $client->id
            ]),
            'document_pdf'  => route('clients.view-document-pdf', [
                'clientId'  => $client->id,
            ]),
        ];

        $attributes = [
            'campaign_id'   => $campaign->id,
            'is_online'     => $campaign->is_online,
            'subject'       => $campaign->subject,
            'from_name'     => $campaign->from_name,
            'from_email'    => $campaign->from_email,
            'template_id'   => $campaign->template_id,
            'param'         => json_encode(array_merge($param, $client->getCustomFieldValues(false) ?? [])),
            'email'         => $client->email,
            'qrcode'        => $client->qrcode,
            'to_name'       => $client->name,
            'to_email'      => $client->email,
            'status'        => Email::STATUS_WAITING,
        ];

        $email = $this->create($attributes);
        $this->sendMailByJob($email);
        return $email;
    }

    public function sendCampaignEmailByClient(
        Client $client,
        int $campaignId,
        array $overrides = [],
        bool $sendNow = true
    ): ?Email {
        $campaign = $this->campaign()->findByAttributes([
            'id' => $campaignId,
        ]);

        if (empty($campaign) || empty($client->email) || !Helper::checkEmailForm($client->email)) {
            return null;
        }

        $client->loadMissing('event');

        $defaultParams = [
            'id'            => $client->id,
            'name'          => $client->name,
            'email'         => $client->email,
            'phone'         => $client->phone,
            'qrcode'        => $client->qrcode,
            'img_qrcode'    => route('clients.view-qrcode-by-id', [
                'id'        => $client->id
            ]),
            'document_pdf'  => route('clients.view-document-pdf', [
                'clientId'  => $client->id,
            ]),
        ];

        $param = array_merge(
            $client->getCustomFieldValues(false) ?? [],
            $defaultParams,
            $overrides
        );

        $identity = [
            'campaign_id' => $campaign->id,
            'email' => $client->email,
            'qrcode' => $client->qrcode,
        ];

        $email = Email::query()
            ->where($identity)
            ->latest('id')
            ->first();

        if ($email && $email->status === Email::STATUS_SENT) {
            return $email->refresh();
        }

        $attributes = [
            'campaign_id'   => $campaign->id,
            'message_id'    => null,
            'subject'       => $campaign->subject,
            'from_name'     => $campaign->from_name,
            'from_email'    => $campaign->from_email,
            'template_id'   => $campaign->template_id,
            'is_online'     => $campaign->is_online,
            'param'         => json_encode($param),
            'email'         => $client->email,
            'qrcode'        => $client->qrcode,
            'to_name'       => $client->name,
            'to_email'      => $client->email,
            'status'        => Email::STATUS_WAITING,
        ];

        if ($email) {
            $email->fill($attributes)->save();
        } else {
            $email = $this->create($attributes);
        }

        if ($sendNow && (bool) $campaign->is_online && !app()->environment('testing')) {
            try {
                $this->sendMailNow($email);
            } catch (\Throwable $th) {
                Log::warning('VIDEC campaign email send failed', [
                    'campaign_id' => $campaign->id,
                    'email_id' => $email->id,
                    'error' => $th->getMessage(),
                ]);
            }
        }

        return $email->fresh();
    }

    public function sendMailByJob(Email $email)
    {
        dispatch(new SendEmailJob($email->campaign->id, $email->id));
        return $email->refresh();
    }

    public function sendMailNow(Email $email)
    {
        Artisan::call("send:mail --campaignId={$email->campaign_id} --emailId={$email->id}");
        return $email->refresh();
    }
}
