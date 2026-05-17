<?php
namespace App\Http\Controllers\Api\V1\Webhooks;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Webhook\PostmarkService;
use Illuminate\Support\Facades\Log;

class PostmarkController extends Controller
{
    public function __construct(PostmarkService $service)
    {
        $this->service = $service;
    }

    public function handlePostmarkWebhook(Request $request)
    {
        Log::info('Postmark Webhook Received', $request->all());
        $webhookRequest = $request->all();
        $webhookTime = $webhookRequest['DeliveredAt'] ?? null;

        if ($webhookRequest['RecordType'] == "") {

        }

        $attributes = [
            'ip_address'        => $request->ip(),
            'server_id'         => $webhookRequest['ServerID'] ?? null,
            'message_id'        => $webhookRequest['MessageID'],
            'message_stream'    => $webhookRequest['MessageStream'],
            'email'             => $webhookRequest['Recipient'] ?? "UNKOWN",
            'tag'               => $webhookRequest['Tag'] ?? null,
            // 'details'           => $webhookRequest['Details'] ?? null,
            'record_time'       => $webhookTime,
            'status'            => $webhookRequest['RecordType'],
            'metadata'          => $webhookRequest['Metadata'] ?? null,
            'response'          => $webhookRequest,
        ];

        $webhook = $this->service->create($attributes);

        /* get email_id */
        $email = $this->service->email()->findByAttributes([
            'message_id' => $webhookRequest['MessageID'],
        ]);
        if (!empty($email)) {
            $this->service->update($webhook->id, [
                'email_id' => $email->id,
            ]);
        }
        /* end */

        return $this->responseSuccess(null, 'Completed');
    }
}
