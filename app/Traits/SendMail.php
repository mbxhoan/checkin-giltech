<?php

namespace App\Traits;

use App\Helpers\Helper;
use App\Mail\SendMailByPostmark;
use SendGrid\Mail\Mail;
use SendGrid\Mail\From;
use SendGrid\Mail\To;
use App\Models\Email;
use App\Mail\SendMailBySendgrid;
use GuzzleHttp\Client as GuzzleClient;
use Postmark\PostmarkClient;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

trait SendMail
{
    /**
     * @param object - Array
     * @return mixed
     */
    /* public function sendMail($objects = [])
    {
        if (config('app.env') == 'production') {
            // Use Sendgrid
            return $this->sendWithGridMail($objects);
        } else {
            // Use Mail Trap
            return $this->sendWithGridMail($objects);
            // return $this->sendWithMailTrap($objects);
        }
    } */

    private function sendSingleEmailByPostmark($newMail)
    {
        $newMail->update([
            'error_log' => null,
        ]);

        $newMail->refresh();

        if ($newMail->status != Email::STATUS_WAITING) {
            return false;
        }

        try {
            $sendMail = $this->makePostmarkClient();
            $ccArray = json_decode($newMail->campaign->cc ?? '[]', true) ?: [];
            $ccEmails = implode(', ', $ccArray);
            $bccArray = json_decode($newMail->campaign->bcc ?? '[]', true) ?: [];
            $bccEmails = implode(', ', $bccArray);
            $params = json_decode($newMail->param, true) ?: [];
            $attachments = [];

            /* galaxy-holding */
            if (in_array($newMail->template_id, [41483667])) {
                $filePath = storage_path('app/public/files/galaxy/GOI-Premium.png');
                $this->appendAttachmentFromPath($filePath, 'Thư mời GOI-Premium.png', 'image/png', $attachments);
            }

            if (in_array($newMail->template_id, [41460773])) {
                $filePath = storage_path('app/public/files/galaxy/GOI-VIP.png');
                $this->appendAttachmentFromPath($filePath, 'Thư mời GOI-VIP.png', 'image/png', $attachments);
            }

            if (in_array($newMail->template_id, [41461290])) {
                $filePath = storage_path('app/public/files/galaxy/Hackathon-mentor.png');
                $this->appendAttachmentFromPath($filePath, 'Thư mời Hackathon mentor.png', 'image/png', $attachments);
            }

            if (in_array($newMail->template_id, [41461040])) {
                $filePath = storage_path('app/public/files/galaxy/Hackathon-ts.png');
                $this->appendAttachmentFromPath($filePath, 'Thư mời Hackathon thí sinh.png', 'image/png', $attachments);
            }

            if (in_array($newMail->template_id, [42906017])) {
                $filePath = storage_path('app/public/files/agenda/TheHonorCeremonyAgenda.pdf');
                $this->appendAttachmentFromPath($filePath, 'The Honor Ceremony Agenda.pdf', 'application/pdf', $attachments);
            }

            if (in_array($newMail->template_id, [43050168])) {
                $filePath = storage_path('app/public/files/agenda/agenda.pdf');
                $this->appendAttachmentFromPath($filePath, 'The Insiders Forum HCMC - Event Agenda.pdf', 'application/pdf', $attachments);
            }

            if (in_array($newMail->template_id, [42559859])) {
                Log::info($params['document_pdf'] ?? 'missing-document_pdf');

                if (isset($params['document_pdf'])) {
                    $fileUrl = $params['document_pdf'];
                    $this->appendAttachmentFromUrl($fileUrl, 'Chứng chỉ.png', 'image/png', $attachments);
                } else {
                    throw new Exception('File not found');
                }
            }

            $sendResult = $this->sendEmailWithTemplateRetry($sendMail, $newMail, $params, $ccEmails, $bccEmails, $attachments);

            $this->info(json_encode($sendResult, JSON_PRETTY_PRINT));
            $statusCode = 202;

            $newMail->update([
                'message_id'      => $sendResult->MessageID,
                'server_response' => json_encode($sendResult),
            ]);
        } catch (Throwable $e) {
            $this->info('Caught exception: '.$e->getMessage()."\n");
            $newMail->update([
                'status'    => Email::STATUS_NEW,
                'error_log' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            return false;
        }

        return $statusCode;
    }

    private function sendEmailWithTemplateRetry(
        PostmarkClient $sendMail,
        $newMail,
        array $params,
        string $ccEmails,
        string $bccEmails,
        array $attachments
    ) {
        $attempts = max(1, (int) config('services.postmark.retry_times', 3));
        $sleepMs = max(100, (int) config('services.postmark.retry_sleep_milliseconds', 1500));
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $sendMail->sendEmailWithTemplate(
                    $newMail->from_email,
                    $newMail->to_email,
                    (int) $newMail->template_id,
                    $params,
                    true,
                    $newMail->campaign->type ?? $newMail->campaign->id,
                    true,
                    $newMail->from_email,
                    $ccEmails,
                    $bccEmails,
                    [],
                    $attachments
                );
            } catch (Throwable $exception) {
                $lastException = $exception;

                if (!$this->isRetryableMailException($exception) || $attempt >= $attempts) {
                    throw $exception;
                }

                Log::warning('Postmark send timeout/retryable failure. Retrying...', [
                    'email_id' => $newMail->id,
                    'to' => $newMail->to_email,
                    'attempt' => $attempt,
                    'attempts' => $attempts,
                    'error' => $exception->getMessage(),
                ]);

                usleep($sleepMs * 1000);
            }
        }

        throw $lastException ?? new Exception('Unknown Postmark send error');
    }

    private function makePostmarkClient(): PostmarkClient
    {
        $token = config('services.postmark.token') ?: env('POSTMARK_TOKEN');
        if (empty($token)) {
            throw new Exception('Missing POSTMARK_TOKEN');
        }

        $requestTimeout = max(10, (int) config('services.postmark.request_timeout_seconds', 120));
        $connectTimeout = max(2, (int) config('services.postmark.connect_timeout_seconds', 10));

        $client = new PostmarkClient($token, $requestTimeout);
        $client->setClient(new GuzzleClient([
            'timeout' => $requestTimeout,
            'connect_timeout' => $connectTimeout,
        ]));

        return $client;
    }

    private function appendAttachmentFromPath(string $filePath, string $fileName, string $contentType, array &$attachments): void
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $fileSize = filesize($filePath);
        $this->ensureAttachmentSize($fileName, $fileSize);

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Failed to read attachment: {$filePath}");
        }

        $attachments[] = [
            'Name' => $fileName,
            'Content' => base64_encode($content),
            'ContentType' => $contentType,
        ];
    }

    private function appendAttachmentFromUrl(string $fileUrl, string $fileName, string $contentType, array &$attachments): void
    {
        $requestTimeout = max(10, (int) config('services.postmark.request_timeout_seconds', 120));
        $connectTimeout = max(2, (int) config('services.postmark.connect_timeout_seconds', 10));
        $retries = max(1, (int) config('services.postmark.retry_times', 3));
        $sleepMs = max(100, (int) config('services.postmark.retry_sleep_milliseconds', 1500));

        $response = Http::connectTimeout($connectTimeout)
            ->timeout($requestTimeout)
            ->retry($retries, $sleepMs)
            ->get($fileUrl);

        if (!$response->successful()) {
            throw new Exception("File not found at URL: {$fileUrl}");
        }

        $fileContent = $response->body();
        $this->ensureAttachmentSize($fileName, strlen($fileContent));

        $attachments[] = [
            'Name' => $fileName,
            'Content' => base64_encode($fileContent),
            'ContentType' => $contentType,
        ];
    }

    private function ensureAttachmentSize(string $fileName, int|false $bytes): void
    {
        if ($bytes === false) {
            throw new Exception("Cannot detect attachment size for {$fileName}");
        }

        $maxBytes = max(1024, (int) config('services.postmark.max_attachment_bytes', 7340032));
        if ($bytes > $maxBytes) {
            $maxMb = round($maxBytes / 1024 / 1024, 1);
            throw new Exception("Attachment {$fileName} too large ({$bytes} bytes). Max {$maxMb}MB. Use download link instead of attachment.");
        }
    }

    private function isRetryableMailException(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'curl error 28')
            || str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'connection reset')
            || str_contains($message, '503')
            || str_contains($message, 'internal server error');
    }

    /* SEND WITH OFFLINE TEMPLATE */
    private function sendSingleOfflineByPostmark($email)
    {
        $email->update([
            'error_log' => null,
        ]);

        if (!Helper::checkTemplateEmail($email->template_id)) {
            $this->error('Template not found');
            $email->update([
                'status' => Email::STATUS_NEW,
                'error_log' => 'Template not found',
            ]);

            return false;
        }

        try {
            $send = new SendMailByPostmark($email->template_id, $email);
            $sendResponse = $send->sendThem();
            $this->info(json_encode($sendResponse, JSON_PRETTY_PRINT));

            $email->update([
                'message_id' => $sendResponse->MessageID,
                'server_response' => json_encode($sendResponse),
            ]);
        } catch (Exception $e) {
            $this->info('Caught exception: '.$e->getMessage()."\n");
            $email->update([
                'status' => Email::STATUS_NEW,
                'error_log' => $e,
            ]);

            return false;
        }

        return 1;
    }
}

// curl "https://api.postmarkapp.com/email/withTemplate" \
//   -X POST \
//   -H "Accept: application/json" \
//   -H "Content-Type: application/json" \
//   -H "X-Postmark-Server-Token: 6a0e57ca-9b5c-4e5e-9d29-ad8c6c405c67" \
//   -d '{
//   "From": "event.infor@giltech.com.vn",
//   "To": "hoan.mx@giltech.com.vn",
//   "TemplateAlias": "user-invitation",
//   "TemplateModel": {
//	"name": "Hoan",
//	"invite_sender_name": "Giltech",
//	"invite_sender_organization_name": "Giltech VN",
//	"product_name": "Ban la",
//	"action_url": "giltech.com.vn",
//	"support_email": "event.infor@giltech.com.vn",
//	"live_chat_url": "giltech.com.vn",
//	"help_url": "giltech.com.vn"
// }
// }'
