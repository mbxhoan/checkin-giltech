<?php

namespace App\Services\Videc;

use App\Models\Event;
use App\Models\EmailLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\PortalUser;
use App\Models\Registration;
use App\Models\RegistrationItem;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly RegistrationFileService $registrationFileService,
    ) {
    }

    public function draft(array $input): Registration
    {
        return DB::transaction(function () use ($input) {
            $input = $this->normalizeInput($input);
            $event = Event::query()->findOrFail($input['event_id']);
            $portalUser = $this->upsertPortalUser($input);
            $registration = $this->upsertRegistration($event->id, $portalUser->id, $input);

            $customFields = (array) ($input['custom_fields'] ?? []);
            if (!empty($customFields)) {
                $customFields = $this->registrationFileService->attachSubmittedFiles(
                    $event,
                    $portalUser,
                    $registration,
                    $customFields,
                    null,
                    false,
                );

                $this->syncRegistrationCustomFields($registration, $customFields);
            }

            return $registration->load(['portalUser', 'event', 'currentOrder', 'items']);
        });
    }

    public function submit(array $input): Registration
    {
        return DB::transaction(function () use ($input) {
            $input = $this->normalizeInput($input);
            $event = Event::query()->findOrFail($input['event_id']);

            $portalUser = $this->upsertPortalUser($input);
            $clientId = isset($input['client_id']) ? (int) $input['client_id'] : null;
            $client = $clientId ? Client::query()->find($clientId) : null;
            $registration = $this->upsertRegistration($event->id, $portalUser->id, $input);
            $items = $this->normalizeItems($input['items'] ?? []);
            $customFields = (array) ($input['custom_fields'] ?? []);
            $customFields = $this->registrationFileService->attachSubmittedFiles(
                $event,
                $portalUser,
                $registration,
                $customFields,
                $client,
                true,
            );

            if (!empty($customFields)) {
                $this->syncRegistrationCustomFields($registration, $customFields);
                $this->syncClientCustomFields($client, $customFields);
            }

            $order = $registration->currentOrder;
            $orderChanged = false;
            $orderLocked = $order && ($order->paymentAttempts()->exists() || in_array($order->status, ['paid', 'cancelled', 'refunded'], true));

            if (!$order) {
                if (empty($items)) {
                    throw ValidationException::withMessages([
                        'items' => 'At least one ticket is required',
                    ]);
                }

                $order = $this->createOrder(
                    $registration,
                    $items,
                    $input['source'] ?? 'registration',
                    $clientId,
                );
                $registration->forceFill(['current_order_id' => $order->id])->save();
                $orderChanged = true;
            } elseif ($orderLocked) {
                $orderChanged = false;
            } elseif (!empty($items)) {
                $orderChanged = $this->syncCurrentOrderItems($order, $registration, $items, $input['source'] ?? 'registration');
            }

            if ($order && $clientId && !$orderLocked && (int) $order->client_id !== $clientId) {
                $order->forceFill(['client_id' => $clientId])->save();
            }

            if ($order && $orderChanged) {
                $this->queueCheckinSync($registration, $order, 'UNPAID');
                $this->recordEmailLog($registration, $order, 'registration_unpaid');
            }

            return $registration->load(['portalUser', 'event', 'currentOrder', 'items']);
        });
    }

    public function createSupplementalOrder(Order $sourceOrder, array $items): Order
    {
        return DB::transaction(function () use ($sourceOrder, $items) {
            $sourceOrder->loadMissing('registration.portalUser', 'registration.event');
            $registration = $sourceOrder->registration;

            if (!$registration) {
                abort(404, 'Registration not found for order');
            }

            $order = $this->createOrder($registration, $items, 'portal_buy_more', $sourceOrder->client_id);
            $registration->forceFill(['current_order_id' => $order->id])->save();

            $this->queueCheckinSync($registration, $order, 'UNPAID');
            $this->recordEmailLog($registration, $order, 'registration_unpaid');

            return $order->fresh(['registration.items', 'registration.portalUser']);
        });
    }

    private function normalizeInput(array $input): array
    {
        $customFields = (array) ($input['custom_fields'] ?? []);

        if (empty($input['phone'])) {
            $input['phone'] = data_get($customFields, 'phone') ?: data_get($customFields, 'phone_number');
        }

        if (empty($input['notes']) && !empty(data_get($customFields, 'reference_id'))) {
            $input['notes'] = (string) data_get($customFields, 'reference_id');
        }

        $input['custom_fields'] = $customFields;

        return $input;
    }

    private function resolveRegistrationStatus(Registration $registration, array $input): string
    {
        $currentOrderStatus = $registration->currentOrder?->status;

        if (in_array($registration->status, ['paid', 'cancelled', 'refunded'], true) || in_array($currentOrderStatus, ['paid', 'pending_payment'], true)) {
            return $registration->status;
        }

        return !empty($input['items']) ? 'registered_unpaid' : 'draft';
    }

    private function upsertPortalUser(array $input): PortalUser
    {
        $portalUser = PortalUser::query()->firstOrCreate(
            ['email' => Str::lower($input['email'])],
            [
                'name' => $input['name'] ?? null,
                'phone' => $input['phone'] ?? null,
                'status' => 'ACTIVE',
                'password' => Hash::make(PortalUser::DEFAULT_PASSWORD),
            ]
        );

        $portalUser->fill([
            'name' => $input['name'] ?? $portalUser->name,
            'phone' => $input['phone'] ?? data_get($input, 'custom_fields.phone') ?? data_get($input, 'custom_fields.phone_number') ?? $portalUser->phone,
            'status' => 'ACTIVE',
        ])->save();

        if (empty($portalUser->password)) {
            $portalUser->forceFill([
                'password' => Hash::make(PortalUser::DEFAULT_PASSWORD),
            ])->save();
        }

        return $portalUser;
    }

    private function upsertRegistration(int $eventId, int $portalUserId, array $input): Registration
    {
        $registration = Registration::query()->firstOrCreate(
            [
                'event_id' => $eventId,
                'portal_user_id' => $portalUserId,
            ],
            [
                'code' => $this->makeRegistrationCode(Event::query()->findOrFail($eventId)),
                'status' => 'draft',
                'checkin_sync_status' => 'pending',
                'metadata' => [],
            ]
        );

        $registration->fill([
            'status' => $this->resolveRegistrationStatus($registration, $input),
            'submitted_at' => !empty($input['items']) && !$registration->submitted_at ? now() : $registration->submitted_at,
            'notes' => $input['notes'] ?? $registration->notes,
            'metadata' => array_merge($registration->metadata ?? [], [
                'source' => $input['source'] ?? 'web',
            ]),
        ])->save();

        return $registration;
    }

    public function createOrder(Registration $registration, array $items, string $source = 'registration', ?int $clientId = null): Order
    {
        $items = $this->normalizeItems($items);
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'At least one ticket is required',
            ]);
        }

        $tickets = Ticket::query()
            ->whereIn('id', collect($items)->pluck('ticket_id')->unique()->values())
            ->get()
            ->keyBy('id');

        $this->validateTicketSelection($registration->event, $items, $tickets);

        $orderSequence = ((int) Order::query()->where('registration_id', $registration->id)->count()) + 1;
        $orderNo = sprintf('VIDEC-%s-%06d-%02d', $registration->event->code, $registration->id, $orderSequence);

        $order = Order::query()->create([
            'client_id' => $clientId,
            'ref_id' => $registration->id,
            'no' => $orderNo,
            'code' => Str::upper(Str::random(12)),
            'token' => null,
            'payment_url' => null,
            'price' => 0,
            'expiry_date' => now()->addMinutes((int) config('onepay.order_expiry_minutes', 15)),
            'ipn' => null,
            'status' => 'unpaid',
            'portal_user_id' => $registration->portal_user_id,
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'promo_code_id' => null,
            'payment_method' => 'onepay',
            'currency' => config('onepay.currency', 'VND'),
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'checkin_sync_status' => 'pending',
            'checkin_reference' => null,
            'metadata' => [
                'source' => $source,
                'payment_method' => 'onepay',
            ],
        ]);

        $subtotal = 0.0;

        foreach ($items as $item) {
            $ticket = $tickets->get($item['ticket_id']);
            $quantity = (int) ($item['quantity'] ?? 1);
            $unitPrice = (float) $ticket->price;
            $lineTotal = $unitPrice * $quantity;

            $subtotal += $lineTotal;

            RegistrationItem::query()->create([
                'registration_id' => $registration->id,
                'order_id' => $order->id,
                'ticket_id' => $ticket->id,
                'ticket_code' => $ticket->code,
                'ticket_name' => $ticket->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'total_amount' => $lineTotal,
                'status' => 'ACTIVE',
                'metadata' => [
                    'event_code' => $ticket->event_code,
                ],
            ]);
        }

        $order->forceFill([
            'price' => $subtotal,
            'subtotal_amount' => $subtotal,
            'total_amount' => $subtotal,
        ])->save();

        return $order;
    }

    private function syncCurrentOrderItems(Order $order, Registration $registration, array $items, string $source = 'registration'): bool
    {
        $items = $this->normalizeItems($items);

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'At least one ticket is required',
            ]);
        }

        $existingItems = $order->registrationItems()
            ->orderBy('id')
            ->get()
            ->map(fn (RegistrationItem $item) => [
                'ticket_id' => (int) $item->ticket_id,
                'quantity' => (int) $item->quantity,
            ])
            ->values()
            ->all();

        $desiredItems = collect($items)
            ->map(fn (array $item) => [
                'ticket_id' => (int) $item['ticket_id'],
                'quantity' => (int) ($item['quantity'] ?? 1),
            ])
            ->values()
            ->all();

        if ($existingItems === $desiredItems) {
            return false;
        }

        // var_dump($order->id);
        if ($order->paymentAttempts()->exists() || in_array($order->status, ['paid', 'cancelled', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'items' => 'Order already has a payment attempt and cannot be modified here',
            ]);
        }

        $tickets = Ticket::query()
            ->whereIn('id', collect($items)->pluck('ticket_id')->unique()->values())
            ->get()
            ->keyBy('id');

        $this->validateTicketSelection($registration->event, $items, $tickets);

        DB::transaction(function () use ($order, $items, $tickets, $source) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->paymentAttempts()->exists() || in_array($order->status, ['paid', 'cancelled', 'refunded'], true)) {
                throw ValidationException::withMessages([
                    'items' => 'Order already has a payment attempt and cannot be modified here',
                ]);
            }

            RegistrationItem::query()
                ->where('order_id', $order->id)
                ->delete();

            $subtotal = 0.0;

            foreach ($items as $item) {
                $ticket = $tickets->get($item['ticket_id']);
                $quantity = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) $ticket->price;
                $lineTotal = $unitPrice * $quantity;

                $subtotal += $lineTotal;

                RegistrationItem::query()->create([
                    'registration_id' => $order->registration_id,
                    'order_id' => $order->id,
                    'ticket_id' => $ticket->id,
                    'ticket_code' => $ticket->code,
                    'ticket_name' => $ticket->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'total_amount' => $lineTotal,
                    'status' => 'ACTIVE',
                    'metadata' => [
                        'event_code' => $ticket->event_code,
                    ],
                ]);
            }

            $order->forceFill([
                'price' => $subtotal,
                'status' => 'unpaid',
                'token' => null,
                'payment_url' => null,
                'promo_code_id' => null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
                'expiry_date' => now()->addMinutes((int) config('onepay.order_expiry_minutes', 15)),
                'metadata' => array_merge($order->metadata ?? [], [
                    'source' => $source,
                    'legacy_payload_synced_at' => now()->toISOString(),
                ]),
            ])->save();
        });

        return true;
    }

    public function normalizeItems(array $items): array
    {
        if (isset($items['ticket_id'])) {
            $items = [$items];
        }

        return array_values(array_filter(array_map(static function ($item) {
            if (!isset($item['ticket_id'])) {
                return null;
            }

            return [
                'ticket_id' => (int) $item['ticket_id'],
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
            ];
        }, $items)));
    }

    private function validateTicketSelection(Event $event, array $items, \Illuminate\Support\Collection $tickets): void
    {
        $seenTicketIds = [];
        $seenGroups = [];

        foreach ($items as $index => $item) {
            $ticket = $tickets->get($item['ticket_id']);

            if (!$ticket) {
                throw ValidationException::withMessages([
                    "items.$index.ticket_id" => 'Selected ticket is invalid',
                ]);
            }

            if ($ticket->event_code !== $event->code) {
                throw ValidationException::withMessages([
                    "items.$index.ticket_id" => 'Selected ticket does not belong to this event',
                ]);
            }

            if (isset($seenTicketIds[$ticket->id])) {
                throw ValidationException::withMessages([
                    'items' => 'Each ticket can only be selected once',
                ]);
            }

            $seenTicketIds[$ticket->id] = true;

            $maxQuantity = (int) data_get($ticket->metadata, 'rules.max_quantity', 0);
            if ($maxQuantity > 0 && (int) ($item['quantity'] ?? 1) > $maxQuantity) {
                throw ValidationException::withMessages([
                    "items.$index.quantity" => 'Quantity exceeds the allowed limit for this ticket',
                ]);
            }

            $groupCode = (string) data_get($ticket->metadata, 'group.code', '');
            if ($groupCode !== '') {
                if (isset($seenGroups[$groupCode])) {
                    throw ValidationException::withMessages([
                        'items' => 'Only one ticket per category can be selected',
                    ]);
                }

                $seenGroups[$groupCode] = true;
            }
        }
    }

    private function makeRegistrationCode(Event $event): string
    {
        return sprintf('REG-%s-%s', $event->code, Str::upper(Str::random(8)));
    }

    private function queueCheckinSync(Registration $registration, Order $order, string $paymentStatus): void
    {
        $payload = [
            'registration_id' => $registration->id,
            'portal_user_id' => $registration->portal_user_id,
            'order_id' => $order->id,
            'ticket_type_id' => $order->registrationItems()->pluck('ticket_id')->filter()->values()->all(),
            'quantity' => $order->registrationItems()->sum('quantity'),
            'payment_status' => $paymentStatus,
            'invoice_no' => null,
            'qr_code' => null,
        ];

        $registration->forceFill([
            'checkin_sync_status' => 'queued',
            'metadata' => array_merge($registration->metadata ?? [], [
                'checkin_sync_payload' => $payload,
                'checkin_sync_queued_at' => now()->toISOString(),
            ]),
        ])->save();

        $order->forceFill([
            'checkin_sync_status' => 'queued',
            'metadata' => array_merge($order->metadata ?? [], [
                'checkin_sync_payload' => $payload,
                'checkin_sync_queued_at' => now()->toISOString(),
            ]),
        ])->save();
    }

    private function recordEmailLog(Registration $registration, Order $order, string $type): void
    {
        $registration->loadMissing('portalUser');

        EmailLog::query()->create([
            'event_id' => $registration->event_id,
            'portal_user_id' => $registration->portal_user_id,
            'registration_id' => $registration->id,
            'order_id' => $order->id,
            'type' => $type,
            'subject' => 'VIDEC 2026 registration received',
            'name' => $registration->portalUser?->name,
            'email' => $registration->portalUser?->email,
            'content' => 'Registration received and waiting for payment.',
            'status' => 'queued',
            'metadata' => [
                'order_no' => $order->no,
                'from' => config('mail.from.address') ?: env('FROM_MAIL'),
                'payment_method' => $order->payment_method,
            ],
        ]);
    }

    private function syncRegistrationCustomFields(Registration $registration, array $customFields): void
    {
        $metadata = $registration->metadata ?? [];
        $metadata['custom_fields'] = array_merge((array) ($metadata['custom_fields'] ?? []), $customFields);

        $registration->forceFill([
            'metadata' => $metadata,
        ])->save();
    }

    private function syncClientCustomFields(?Client $client, array $customFields): void
    {
        if (!$client) {
            return;
        }

        $client->forceFill([
            'custom_fields' => array_merge((array) ($client->custom_fields ?? []), $customFields),
        ])->save();
    }
}
