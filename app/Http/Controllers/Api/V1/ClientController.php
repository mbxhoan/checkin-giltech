<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Clients\FindByQrcodeRequest;
use App\Http\Requests\Api\Clients\FindRequest;
use App\Http\Requests\Api\Clients\GenerateQrcodeOnSettingRequest;
use App\Http\Requests\Api\Clients\StoreRequest;
use App\Http\Requests\Api\Clients\UpsertByIdRequest;
use App\Http\Requests\Api\Clients\UpsertRequest;
use App\Services\Api\ClientService;
use App\Http\Resources\ClientWithEvent as ClientWithEventResource;
use App\Http\Resources\Client as ClientResource;
use App\Http\Resources\ClientCollection;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Services\Videc\RegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function __construct(
        ClientService $service,
        private readonly RegistrationService $registrationService
    ) {
        $this->service = $service;
    }

    public function test()
    {
        $clients = $this->service->getListByAttributes([
            "event_id" => 66
        ]);

        return (new ClientCollection($clients))
            ->additional(['fetched_at' => now()]);
    }

    public function testSync(Request $request)
    {
        return $this->responseSuccess([
            'total'     => $request->items ? count($request->items) : 0,
            'datas'     => $request->all(),
        ], "Đã đồng bộ dữ liệu thành công");
    }

    public function generateQrcodeOnSetting(Event $event, GenerateQrcodeOnSettingRequest $request)
    {
        $attributes = $request->only([
            'name',
            'email',
            'custom_fields',
        ]);

        return $event->generateQrcodeOnSetting(
            $event->code,
            $attributes['custom_fields']['phone'] ?? null,
            $attributes['email'] ?? null,
            $attributes['phone'] ?? null,
            $attributes['custom_fields'] ?? []
        );
    }

    public function find(FindRequest $request)
    {
        $defaults = [
            'event_id',
            'event_code',
            'qrcode',
            'id',
            'name',
            'email',
        ];

        // Split the request into main and custom fields
        $input = $request->all();
        $mainAttributes = array_filter($request->only($defaults));

        // Anything not in defaults is considered a custom field
        $customFields = collect($input)
            ->except($defaults)
            ->filter(fn($v) => !is_null($v) && $v !== '');

        // Start the query
        $query = Client::query();

        // Apply main field filters
        foreach ($mainAttributes as $key => $value) {
            $query->where($key, $value);
        }

        // Apply custom_fields filters (JSON column)
        foreach ($customFields as $key => $value) {
            $query->where("custom_fields->{$key}", $value);
        }

        $client = $query->first();

        if ($client) {
            return $this->responseSuccess(new ClientWithEventResource($client), "Khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }

    public function register(StoreRequest $request)
    {
        $client = null;
        $msg = "Tạo mới thành công";
        $attributes = $request->only([
            'id',
            'event_code',
            'qrcode',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]);

        $event = $this->service->event()->findByAttributes([
            'code' => $attributes['event_code'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        if (isset($attributes['id'])) {
            $id = $attributes['id'];
            $msg = "Cập nhật thông tin thành công";
            $client = $this->service->findByAttributes([
                'event_code'    => $event->code,
                'id'            => $id,
            ]);

            Log::info("Updated client {$client->qrcode}: ".$client);

            $client->update([
                'name'          => $attributes['name'],
                'email'         => $attributes['email'] ?? null,
                'lp_id'         => $attributes['lp_id'] ?? null,
                'custom_fields' => $attributes['custom_fields'],
            ]);

            return $this->responseSuccess(new ClientWithEventResource($client), "Đã cập nhật thông tin thành công");
        }

        $customFields = $attributes['custom_fields'] ?? [];
        $attributes['event_id'] = $event->id;
        $attributes['event_code'] = $event->code;
        $attributes['register_source'] = "LANDING_PAGE";

        /* customize */
        /* long-kan */
        if ($event->code == "long-kan") {
            $attributes['custom_fields']['lucky'] = $event->clients->count() == 0 ? "0001" : str_pad((int)$event->clients->last()->custom_fields['lucky'] + 1, 4, "0", STR_PAD_LEFT);
        }
        /* VIETNAMWATERWEEK2025 */
        if ($event->code == "VIETNAMWATERWEEK2025") {
            $oldNumber = $event->clients->last()->custom_fields['number'] ?? "VWW-00001";
            $oldNumber = substr($oldNumber, 4);
            $attributes['custom_fields']['number'] = "VWW-".($event->clients->count() == 0 ? "00001" : str_pad((int)$oldNumber + 1, 5, "0", STR_PAD_LEFT));
        }

        /* get qrcode if empty */
        if (!isset($attributes['qrcode'])) {
            $attributes['qrcode'] = $event->generateQrcodeOnSetting($event->code, $customFields['phone'] ?? null, $attributes['email'] ?? null, $attributes['name'], $customFields ?? []);

            /* customize */
            /* phi-lao-mims-811 */
            if ($event->code == "phi-lao-mims-811") {
                $taitro = $attributes['custom_fields']['taitro'];
                if (!empty($taitro)) {
                    $taitro = strtoupper($taitro);
                    $attributes['type'] = $taitro;
                    $lastClient = $this->service->findByAttributes([
                        'event_id'      => $event->id,
                        'type'          => $taitro,
                    ], [], [
                        'qrcode'        => $taitro
                    ], [
                        'id'            => 'DESC',
                        'qrcode'        => 'DESC',
                    ]);

                    if ($lastClient) {
                        $qrcode = Helper::nextCode($lastClient->qrcode);

                        if (!empty($qrcode)) {
                            $attributes['qrcode'] = $qrcode;
                        }
                    } else {
                        $attributes['qrcode'] = "{$taitro}001";
                    }

                    // $lastTaiTroNum = $lastClient->custom_fields['taitro'] ?? 0;
                    // $currentTaiTroNum = $lastTaiTroNum + 1;
                    // $currentTaiTroNum = str_pad($currentTaiTroNum, 3, "0", STR_PAD_LEFT);
                    // $attributes['qrcode'] = "{$taitro}{$currentTaiTroNum}";
                }
            }
        }

        /* handle files */
        // $typeFiles = $event->getCustomFieldTemplates(false, null, null, [
        //     'type' => CustomFieldTemplate::TYPE_FILE
        // ]);

        // if (count($typeFiles)) {
        //     foreach ($typeFiles as $key => $detail) {
        //         if ($request->hasFile("custom_fields.{$key}")) {
        //             $files = $request->file("custom_fields.{$key}");
        //             $attributes['custom_fields'][$key] = [];
        //             foreach ($files as $file) {
        //                 if ($file->isValid()) {
        //                     $filename = time().'_'.$file->getClientOriginalName();
        //                     $path = $file->storeAs('uploads', $filename, 'public');
        //                     $attributes['custom_fields'][$key][] = $path;
        //                 }
        //             }
        //         }
        //     }
        // }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $client->refresh();
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
        }

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        /* register */
        $this->service->attributes['campaign_id'] = $request->campaign_id ?? null;
        $this->service->attributes['card_id'] = $request->card_id ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $result = $this->service->register($event, $client);

        // $landingPage = $this->service->landing_page()->findByAttributes([
        //     'slug' => $slug,
        // ]);

        // if ($landingPage) {
        //     return redirect()->route('landing_pages.success', [
        //         'slug'      => $landingPage->slug,
        //         'qrcode'    => $client->qrcode
        //     ])->with('success', $result['msg']);
        // }

        return $this->responseSuccess(new ClientWithEventResource($client->refresh()), $result['msg']);
        return redirect()->route('home')->with('success', $msg);
    }

    public function search(Request $request)
    {
        $client = null;
        $attributes = $request->only([
            'event_id',
            'qrcode',
            'name',
            'email',
            'status',
            'custom_fields',
        ]);

        $event = $this->service->event()->findByAttributes([
            'id' => $attributes['event_id'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        $customFields = $attributes['custom_fields'] ?? [];
        $query = Client::query();

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        // Filter by name
        if ($request->filled('qrcode')) {
            $query->where('qrcode', $request->input('qrcode'));
        }

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }

        // Filter inside JSON column (custom_fields)
        if (count($customFields)) {
            foreach ($customFields as $key => $value) {
                $query->where("custom_fields->$key", $value);
            }
        }

        $client = $query->first();

        if (!empty($client)) {
            return $this->responseSuccess(new ClientWithEventResource($client), "Đã tìm thấy thông tin");
        }

        return $this->responseError("Không tìm thấy thông tin");
    }

    public function upsert(UpsertRequest $request)
    {
        $client = null;
        $msg = "Tạo mới thành công";
        $attributes = array_filter($request->only([
            'event_id',
            'qrcode',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]));

        $event = $this->service->event()->findByAttributes([
            'id' => $attributes['event_id'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        if (!$this->service->user()->validateApiUser($request->getUser(), $event)) {
            return $this->responseError("Thông tin không hợp lệ", 404);
        }

        $customFields = $this->normalizeLegacyCustomFields((array) ($attributes['custom_fields'] ?? []));
        $attributes['custom_fields'] = $customFields;
        $attributes['event_id'] = $event->id;
        $attributes['event_code'] = $event->code;
        $attributes['register_source'] = Client::REGISTER_API;

        if (isset($attributes['qrcode'])) {
            $qrcode = $attributes['qrcode'];
            $client = $this->service->findByAttributes([
                'event_code'    => $event->code,
                'qrcode'        => $qrcode,
            ]);
        } else {
            /* get qrcode if empty */
            $attributes['qrcode'] = $event->generateQrcodeOnSetting(
                $event->code,
                $customFields['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['name'],
                $customFields ?? []
            );
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
            /* generate img_qrcode */
            $this->service->update($client->id, [
                'img_qrcode' => $client->generateImgQrcode(),
            ]);
        }

        $client->refresh();

        /* register */
        /* hidec-2025 */
        $this->service->attributes['campaign_id'] = ($event->id == 39 ? 120 : null) ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $this->service->register($event, $client);
        return $this->responseSuccess(new ClientResource($client->refresh()), $msg);
    }

    public function upsertById(UpsertByIdRequest $request)
    {
        $client = null;
        $createdClient = false;
        $msg = "Tạo mới thành công";
        $attributes = array_filter($request->only([
            'id',
            'event_id',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]));

        $event = $this->service->event()->findByAttributes([
            'id' => $attributes['event_id'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        if (!$this->service->user()->validateApiUser($request->getUser(), $event)) {
            return $this->responseError("Thông tin không hợp lệ", 404);
        }

        $customFields = $this->normalizeLegacyCustomFields((array) ($attributes['custom_fields'] ?? []));
        $attributes['custom_fields'] = $customFields;
        $attributes['event_id'] = $event->id;
        $attributes['event_code'] = $event->code;
        $attributes['register_source'] = Client::REGISTER_API;

        if (isset($attributes['id'])) {
            $id = $attributes['id'];
            $client = $this->service->findByAttributes([
                'id'        => $id,
            ]);
        } else {
            /* get qrcode if empty */
            $attributes['qrcode'] = $event->generateQrcodeOnSetting(
                $event->code,
                $customFields['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['name'],
                $customFields ?? []
            );
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $client->refresh();
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
            $createdClient = true;
            /* generate img_qrcode */
            $this->service->update($client->id, [
                'img_qrcode' => $client->generateImgQrcode(),
            ]);
        }

        if ($request->filled('items')) {
            $response = DB::transaction(function () use ($request, $event, $attributes, $client, $msg) {
                $registration = $this->registrationService->submit(
                    $this->mapLegacyRegistrationPayload($request, $event, $attributes, $client->id)
                );
                $registration->load([
                    'portalUser',
                    'event',
                    'items.ticket',
                    'currentOrder.paymentAttempts',
                    'currentOrder.cashPaymentLogs.cashier',
                    'currentOrder.invoice',
                    'currentOrder.registrationItems.ticket',
                    'currentOrder.ticketIssuances',
                ]);

                $clientData = (new ClientResource($client->refresh()))->toArray($request);
                $clientData['registration'] = $this->legacyRegistrationSnapshot($registration);
                $clientData['order'] = $this->legacyOrderSnapshot($registration->currentOrder);
                $clientData['tickets'] = $this->legacyTicketSummary($registration->items);

                return $this->responseSuccess($clientData, $msg);
            });

            if ($createdClient && $event->code === 'videc-2026') {
                $this->sendVidecRegistrationEmail($client->refresh());
            }

            return $response;
        }

        /* register */
        /* hidec-2025 */
        $this->service->attributes['campaign_id'] = ($event->id == 39 ? 120 : null) ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $this->service->register($event, $client);

        if ($createdClient && $event->code === 'videc-2026') {
            $this->sendVidecRegistrationEmail($client->refresh());
        }

        return $this->responseSuccess(new ClientResource($client->refresh()), $msg);
    }

    private function sendVidecRegistrationEmail(Client $client): void
    {
        try {
            $this->service->middleware_email()->sendCampaignEmailByClient($client, 307, [
                'name' => $client->name,
                'email' => $client->email,
            ]);
        } catch (\Throwable $th) {
            Log::warning('VIDEC 2026 registration campaign email failed', [
                'client_id' => $client->id,
                'event_code' => $client->event_code,
                'error' => $th->getMessage(),
            ]);
        }
    }

    private function normalizeLegacyCustomFields(array $customFields): array
    {
        if (!isset($customFields['phone']) && !empty($customFields['phone_number'])) {
            $customFields['phone'] = $customFields['phone_number'];
        }

        if (!isset($customFields['workplace']) && !empty($customFields['company_name'])) {
            $customFields['workplace'] = $customFields['company_name'];
        }

        if (!isset($customFields['job']) && !empty($customFields['job_title'])) {
            $customFields['job'] = $customFields['job_title'];
        }

        if (!isset($customFields['dob']) && !empty($customFields['date_of_birth'])) {
            $customFields['dob'] = $customFields['date_of_birth'];
        }

        return $customFields;
    }

    private function mapLegacyRegistrationPayload(Request $request, Event $event, array $attributes, ?int $clientId = null): array
    {
        $customFields = (array) ($attributes['custom_fields'] ?? []);

        return [
            'event_id' => $event->id,
            'client_id' => $clientId,
            'email' => $attributes['email'] ?? null,
            'name' => $attributes['name'],
            'phone' => $customFields['phone'] ?? $customFields['phone_number'] ?? null,
            'notes' => $customFields['reference_id'] ?? null,
            'source' => 'api.v1.clients.upsert-by-id',
            'items' => $request->input('items', []),
            'custom_fields' => $customFields,
        ];
    }

    private function legacyRegistrationSnapshot($registration): array
    {
        return [
            'id' => $registration->id,
            'code' => $registration->code,
            'event_id' => $registration->event_id,
            'portal_user_id' => $registration->portal_user_id,
            'current_order_id' => $registration->current_order_id,
            'status' => $registration->status,
            'notes' => $registration->notes,
            'metadata' => $registration->metadata,
            'items' => $this->legacyTicketSummary($registration->items ?? collect()),
            'current_order' => $this->legacyOrderSnapshot($registration->currentOrder),
        ];
    }

    private function legacyOrderSnapshot($order): ?array
    {
        if (!$order) {
            return null;
        }

        return [
            'id' => $order->id,
            'client_id' => $order->client_id,
            'registration_id' => $order->registration_id,
            'event_id' => $order->event_id,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'currency' => $order->currency,
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'payment_url' => $order->payment_url,
            'payment_attempts' => $order->paymentAttempts ?? collect(),
            'cash_payment_logs' => collect($order->cashPaymentLogs ?? collect())->map(function ($log) {
                return [
                    'id' => $log->id,
                    'amount_due' => (float) $log->amount_due,
                    'amount_received' => (float) $log->amount_received,
                    'change_amount' => (float) $log->change_amount,
                    'receipt_code' => $log->receipt_code,
                    'confirmed_at' => $log->confirmed_at,
                    'cashier_user' => $log->cashier ? [
                        'id' => $log->cashier->id,
                        'name' => $log->cashier->name,
                        'email' => $log->cashier->email,
                    ] : null,
                ];
            })->values()->all(),
            'invoice' => $order->invoice,
            'registration_items' => $this->legacyTicketSummary($order->registrationItems ?? collect()),
            'ticket_issuances' => $order->ticketIssuances ?? collect(),
        ];
    }

    private function legacyTicketSummary($items): array
    {
        return collect($items)->map(function ($item) {
            $ticket = $item->ticket ?? null;

            return [
                'registration_item_id' => $item->id,
                'ticket_id' => $item->ticket_id,
                'ticket_code' => $item->ticket_code,
                'ticket_name' => $item->ticket_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'discount_amount' => (string) $item->discount_amount,
                'total_amount' => (string) $item->total_amount,
                'ticket' => $ticket ? [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'name' => $ticket->name,
                    'type' => $ticket->type,
                    'price' => (string) $ticket->price,
                    'dates_string' => $ticket->dates_string,
                    'dates_valid' => $ticket->dates_valid,
                    'metadata' => $ticket->metadata,
                ] : null,
            ];
        })->values()->all();
    }

    public function findByQrcode(FindByQrcodeRequest $request)
    {
        $attributes = array_filter($request->only([
            'event_id',
            'qrcode',
        ]));

        $client = $this->service->findByAttributes($attributes);

        if ($client) {
            if (!$this->service->user()->validateApiUser($request->getUser(), $client->event)) {
                return $this->responseError("Thông tin không hợp lệ", 404);
            }

            return $this->responseSuccess(new ClientResource($client), "Thông tin khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }

    public function findById(int $id, Request $request)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            if (!$this->service->user()->validateApiUser($request->getUser(), $client->event)) {
                return $this->responseError("Thông tin không hợp lệ", 404);
            }

            return $this->responseSuccess(new ClientResource($client), "Thông tin khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'integer',
                'exists:clients,id',
            ],
        ]);

        $attributes = array_filter($request->only([
            'name',
            'email',
            'custom_fields'
        ]));

        $client = $this->service->findByAttributes([
            'id'        => $request->id,
        ]);

        if ($client) {
            $this->service->update($client->id, $attributes);
            return $this->responseSuccess(new ClientResource($client->refresh()), "Cập nhật thành công");
        }

        return $this->responseError("Không tìm thấy thông tin", 404);
    }
}
