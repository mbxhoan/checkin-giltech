<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\ClientService;
use App\Services\Api\ClientUpserter;
use Illuminate\Http\Request;

/* webhook wordpress */
/* hidec 2025 */
class WooHookController extends Controller
{
    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $r, ClientUpserter $svc)
    {
        $payload = $r->getContent() ?? '';
        $sent = (string) $r->header('X-WC-Webhook-Signature', '');
        $calc = base64_encode(hash_hmac('sha256', $payload, "uHmmTwuTOo", true));

        if (!hash_equals($calc, $sent)) {
            return response()->json(['message'=>'bad signature'], 401);
        }

        /* TEST */
        /* return here */
        return response()->json(['ok'=>true]);

        $o = json_decode($payload, true) ?: [];
        $billing = $o['billing'] ?? [];
        $meta = collect($o['meta_data'] ?? [])
            ->mapWithKeys(fn($m)=>[$m['key']=>$m['value']]);

        $client = [
            'full_name' => trim(($billing['first_name']??'').' '.($billing['last_name']??'')),
            'email'     => $billing['email'] ?? null,
            'phone'     => $billing['phone'] ?? null,
            'address'   => $billing['address_1'] ?? null,
            'job'       => $meta['billing_wooccm11'] ?? null,
            'dob'       => $meta['billing_wooccm12'] ?? null,
            'cme'       => $meta['billing_wooccm13'] ?? null,
            'workplace' => $billing['company'] ?? ($meta['billing_company'] ?? null),
            'invoice'   => [
                'flag'    => $meta['billing_wooccm15'] ?? null,
                'name'    => $meta['billing_wooccm17'] ?? null,
                'address' => $meta['billing_wooccm18'] ?? null,
                'tax'     => $meta['billing_wooccm19'] ?? null,
            ],
            'order'     => [
                'id'    => $o['id'] ?? null,
                'total' => $o['total'] ?? null,
                'status'=> $o['status'] ?? null,
            ],
        ];

        // CÁCH 1: lưu trực tiếp DB (khuyến nghị)
        $svc->upsert($client);

        // CÁCH 2: nếu bắt buộc gọi HTTP tới API có sẵn
        // Http::asJson()->post(config('services.core.client_upsert'), $client);

        return response()->json(['ok'=>true]);
    }
}
