<?php

namespace App\Services\Videc;

use App\Models\Client;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketAnalyticsService
{
    private const ACTIVE_ORDER_STATUSES = ['paid', 'unpaid', 'pending_payment'];
    private const UNPAID_ORDER_STATUSES = ['unpaid', 'pending_payment'];

    public function __construct(
        private readonly TicketCatalogService $ticketCatalogService,
    ) {
    }

    public function forEvent(Event $event): array
    {
        $orders = Order::query()
            ->where('event_id', $event->id)
            ->with(['registrationItems.ticket'])
            ->orderByDesc('created_at')
            ->get();

        $catalog = collect($this->ticketCatalogService->forEvent($event)['tickets'] ?? [])->keyBy('id');
        $summary = $this->summarizeOrders($orders);
        $tickets = $this->buildTicketRows($orders, $catalog);
        $topTicket = collect($tickets)->first();

        return [
            'event' => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
            ],
            'summary' => array_merge($summary, [
                'top_ticket' => $topTicket,
                'formatted_paid_revenue' => $this->formatMoney($summary['paid_revenue']),
                'formatted_unpaid_revenue' => $this->formatMoney($summary['unpaid_revenue']),
                'formatted_gross_revenue' => $this->formatMoney($summary['gross_revenue']),
                'formatted_top_ticket_revenue' => $topTicket ? $this->formatMoney((float) ($topTicket['revenue'] ?? 0)) : null,
            ]),
            'charts' => [
                'revenue_status' => [
                    'labels' => ['Đã thanh toán', 'Chưa thanh toán'],
                    'values' => [
                        $summary['paid_revenue'],
                        $summary['unpaid_revenue'],
                    ],
                    'colors' => [
                        'rgba(16, 185, 129, 0.9)',
                        'rgba(245, 158, 11, 0.9)',
                    ],
                ],
                'order_status' => [
                    'labels' => ['Đã thanh toán', 'Chưa thanh toán', 'Đang chờ thanh toán', 'Đã hủy', 'Đã hoàn tiền'],
                    'values' => [
                        $summary['paid_orders'],
                        $summary['unpaid_orders'],
                        $summary['pending_payment_orders'],
                        $summary['cancelled_orders'],
                        $summary['refunded_orders'],
                    ],
                    'colors' => [
                        'rgba(16, 185, 129, 0.9)',
                        'rgba(245, 158, 11, 0.9)',
                        'rgba(59, 130, 246, 0.9)',
                        'rgba(239, 68, 68, 0.9)',
                        'rgba(107, 114, 128, 0.9)',
                    ],
                ],
                'ticket_quantity' => [
                    'labels' => collect($tickets)->pluck('display_name')->values()->all(),
                    'values' => collect($tickets)->pluck('quantity')->values()->all(),
                    'colors' => $this->palette(count($tickets)),
                ],
                'ticket_revenue' => [
                    'labels' => collect($tickets)->pluck('display_name')->values()->all(),
                    'values' => collect($tickets)->pluck('revenue')->values()->all(),
                    'colors' => $this->palette(count($tickets), 0.75),
                ],
            ],
            'tickets' => $tickets,
        ];
    }

    public function clientSummary(Client $client): array
    {
        $history = $this->clientHistory($client);
        $orders = collect($history['orders'] ?? []);
        $latestOrder = $orders->first();

        return [
            'client' => $history['client'],
            'summary' => $history['summary'],
            'latest_order' => $latestOrder,
            'orders' => $orders->take(2)->values()->all(),
        ];
    }

    public function clientSummaries(Collection $clients): array
    {
        return $clients
            ->mapWithKeys(function (Client $client) {
                return [$client->id => $this->clientHistory($client)];
            })
            ->all();
    }

    public function clientHistory(Client $client): array
    {
        $orders = $this->clientOrders($client);
        $summary = $this->summarizeOrders($orders);
        $orderRows = $orders->map(function (Order $order) {
            $items = $order->registrationItems->map(function ($item) {
                $ticket = $item->ticket;

                return [
                    'ticket_id' => $ticket?->id ?? $item->ticket_id,
                    'ticket_code' => $ticket?->code ?? $item->ticket_code,
                    'ticket_name' => $ticket?->name ?? $item->ticket_name,
                    'display_name' => $this->ticketDisplayName($ticket, $item->ticket_name, $item->ticket_code),
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_amount' => (float) $item->total_amount,
                    'formatted_total' => $this->formatMoney((float) $item->total_amount),
                ];
            })->values()->all();

            return [
                'id' => $order->id,
                'no' => $order->no,
                'status' => $order->status,
                'status_label' => $this->orderStatusLabel($order->status),
                'status_class' => $this->orderStatusClass($order->status),
                'total_amount' => (float) ($order->total_amount ?: $order->price ?: 0),
                'formatted_total' => $this->formatMoney((float) ($order->total_amount ?: $order->price ?: 0)),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'payment_attempts' => $order->paymentAttempts?->count() ?? 0,
                'items' => $items,
                'item_text' => collect($items)
                    ->map(fn (array $item) => "{$item['display_name']} x{$item['quantity']}")
                    ->implode(' • '),
            ];
        })->values()->all();

        return [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'event_id' => $client->event_id,
                'qrcode' => $client->qrcode,
            ],
            'summary' => $summary,
            'orders' => $orderRows,
            'ticket_lines' => $this->aggregateClientTickets($orders),
        ];
    }

    public function clientOrders(Client $client): Collection
    {
        $email = mb_strtolower(trim((string) ($client->email ?? '')));

        $query = Order::query()
            ->where('event_id', $client->event_id)
            ->where(function ($builder) use ($client, $email) {
                $builder->where('client_id', $client->id);

                if ($email !== '') {
                    $builder->orWhereHas('registration.portalUser', function ($portalQuery) use ($email) {
                        $portalQuery->whereRaw('LOWER(email) = ?', [$email]);
                    });
                }
            })
            ->with(['registrationItems.ticket', 'paymentAttempts'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return $query->get();
    }

    private function summarizeOrders(Collection $orders): array
    {
        $statusCounts = $orders->countBy('status');

        $paidRevenue = $this->sumOrders($orders->where('status', 'paid'));
        $unpaidRevenue = $this->sumOrders($orders->whereIn('status', self::UNPAID_ORDER_STATUSES));

        return [
            'total_orders' => $orders->count(),
            'paid_orders' => (int) ($statusCounts['paid'] ?? 0),
            'unpaid_orders' => (int) (($statusCounts['unpaid'] ?? 0) + ($statusCounts['pending_payment'] ?? 0)),
            'pending_payment_orders' => (int) ($statusCounts['pending_payment'] ?? 0),
            'cancelled_orders' => (int) ($statusCounts['cancelled'] ?? 0),
            'refunded_orders' => (int) ($statusCounts['refunded'] ?? 0),
            'paid_revenue' => $paidRevenue,
            'unpaid_revenue' => $unpaidRevenue,
            'gross_revenue' => round($paidRevenue + $unpaidRevenue, 2),
            'formatted_paid_revenue' => $this->formatMoney($paidRevenue),
            'formatted_unpaid_revenue' => $this->formatMoney($unpaidRevenue),
            'formatted_gross_revenue' => $this->formatMoney(round($paidRevenue + $unpaidRevenue, 2)),
        ];
    }

    private function buildTicketRows(Collection $orders, Collection $catalog): array
    {
        $rows = [];

        foreach ($catalog as $catalogTicket) {
            $rows[(int) $catalogTicket['id']] = [
                'ticket_id' => (int) $catalogTicket['id'],
                'code' => $catalogTicket['code'],
                'name' => $catalogTicket['name'],
                'display_name' => $catalogTicket['display_name'],
                'quantity' => 0,
                'paid_quantity' => 0,
                'unpaid_quantity' => 0,
                'orders_count' => 0,
                'paid_revenue' => 0.0,
                'unpaid_revenue' => 0.0,
                'revenue' => 0.0,
            ];
        }

        foreach ($orders->whereIn('status', self::ACTIVE_ORDER_STATUSES) as $order) {
            foreach ($order->registrationItems as $item) {
                $ticketId = (int) $item->ticket_id;
                $ticket = $item->ticket;

                if (!isset($rows[$ticketId])) {
                    $rows[$ticketId] = [
                        'ticket_id' => $ticketId,
                        'code' => $ticket?->code ?? $item->ticket_code ?? "ticket-{$ticketId}",
                        'name' => $ticket?->name ?? $item->ticket_name ?? "Ticket {$ticketId}",
                        'display_name' => $this->ticketDisplayName($ticket, $item->ticket_name, $item->ticket_code),
                        'quantity' => 0,
                        'paid_quantity' => 0,
                        'unpaid_quantity' => 0,
                        'orders_count' => 0,
                        'paid_revenue' => 0.0,
                        'unpaid_revenue' => 0.0,
                        'revenue' => 0.0,
                    ];
                }

                $quantity = (int) $item->quantity;
                $revenue = (float) ($item->total_amount ?: ((float) $item->unit_price * $quantity));

                $rows[$ticketId]['quantity'] += $quantity;
                $rows[$ticketId]['revenue'] += $revenue;
                $rows[$ticketId]['orders_count'] += 1;

                if ($order->status === 'paid') {
                    $rows[$ticketId]['paid_quantity'] += $quantity;
                    $rows[$ticketId]['paid_revenue'] += $revenue;
                } else {
                    $rows[$ticketId]['unpaid_quantity'] += $quantity;
                    $rows[$ticketId]['unpaid_revenue'] += $revenue;
                }
            }
        }

        $ticketRows = collect($rows)
            ->sort(function (array $left, array $right) {
                if ($left['quantity'] !== $right['quantity']) {
                    return $right['quantity'] <=> $left['quantity'];
                }

                if ($left['revenue'] !== $right['revenue']) {
                    return $right['revenue'] <=> $left['revenue'];
                }

                return strcmp($left['display_name'], $right['display_name']);
            })
            ->values()
            ->map(function (array $row, int $index) {
                $row['rank'] = $index + 1;
                $row['formatted_revenue'] = $this->formatMoney($row['revenue']);
                $row['formatted_paid_revenue'] = $this->formatMoney($row['paid_revenue']);
                $row['formatted_unpaid_revenue'] = $this->formatMoney($row['unpaid_revenue']);
                return $row;
            })
            ->all();

        return $ticketRows;
    }

    private function aggregateClientTickets(Collection $orders): array
    {
        $rows = [];

        foreach ($orders as $order) {
            foreach ($order->registrationItems as $item) {
                $ticket = $item->ticket;
                $ticketId = (int) $item->ticket_id;
                $quantity = (int) $item->quantity;
                $amount = (float) ($item->total_amount ?: ((float) $item->unit_price * $quantity));

                if (!isset($rows[$ticketId])) {
                    $rows[$ticketId] = [
                        'ticket_id' => $ticketId,
                        'ticket_code' => $ticket?->code ?? $item->ticket_code,
                        'ticket_name' => $ticket?->name ?? $item->ticket_name,
                        'display_name' => $this->ticketDisplayName($ticket, $item->ticket_name, $item->ticket_code),
                        'quantity' => 0,
                        'revenue' => 0.0,
                        'formatted_revenue' => $this->formatMoney(0),
                    ];
                }

                $rows[$ticketId]['quantity'] += $quantity;
                $rows[$ticketId]['revenue'] += $amount;
                $rows[$ticketId]['formatted_revenue'] = $this->formatMoney($rows[$ticketId]['revenue']);
            }
        }

        return collect($rows)
            ->sortByDesc('quantity')
            ->values()
            ->all();
    }

    private function ticketDisplayName(?Ticket $ticket, ?string $fallbackName = null, ?string $fallbackCode = null): string
    {
        if ($ticket) {
            $metadata = (array) ($ticket->metadata ?? []);
            $display = (array) data_get($metadata, 'display', []);
            $nameVi = (string) data_get($display, 'name_vi', $ticket->name);
            $nameEn = (string) data_get($display, 'name_en', $ticket->name);

            if ($nameVi && $nameEn && $nameVi !== $nameEn) {
                return trim("{$nameVi} / {$nameEn}");
            }

            return $nameVi ?: $nameEn;
        }

        return $fallbackName ?: $fallbackCode ?: 'Ticket';
    }

    private function orderStatusLabel(string $status): string
    {
        return [
            'paid' => 'Đã thanh toán',
            'unpaid' => 'Chưa thanh toán',
            'pending_payment' => 'Đang chờ thanh toán',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
        ][$status] ?? Str::headline($status);
    }

    private function orderStatusClass(string $status): string
    {
        return [
            'paid' => 'bg-success',
            'unpaid' => 'bg-warning text-dark',
            'pending_payment' => 'bg-info text-dark',
            'cancelled' => 'bg-danger',
            'refunded' => 'bg-secondary',
        ][$status] ?? 'bg-light text-dark';
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' VND';
    }

    private function orderAmount(Order $order): float
    {
        return (float) ($order->total_amount ?: $order->price ?: 0);
    }

    private function sumOrders(Collection $orders): float
    {
        return round($orders->sum(fn (Order $order) => $this->orderAmount($order)), 2);
    }

    private function palette(int $count, float $alpha = 0.9): array
    {
        $base = [
            [59, 130, 246],
            [16, 185, 129],
            [245, 158, 11],
            [239, 68, 68],
            [139, 92, 246],
            [14, 165, 233],
            [236, 72, 153],
            [107, 114, 128],
            [34, 197, 94],
            [168, 85, 247],
        ];

        if ($count <= 0) {
            return [];
        }

        $colors = [];

        for ($i = 0; $i < $count; $i++) {
            [$r, $g, $b] = $base[$i % count($base)];
            $colors[] = "rgba({$r}, {$g}, {$b}, {$alpha})";
        }

        return $colors;
    }
}
