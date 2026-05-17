<?php

namespace App\Services\Videc;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Collection;

class TicketCatalogService
{
    public function forEvent(Event $event): array
    {
        $tickets = Ticket::query()
            ->where('event_code', $event->code)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $ticketGroups = $this->groupTickets($tickets);

        return [
            'event' => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
            ],
            'rules' => [
                'min_select' => $tickets->isEmpty() ? 0 : 1,
                'max_select' => $ticketGroups->count(),
                'one_per_group' => true,
                'max_quantity_per_ticket' => 1,
                'price_currency' => 'VND',
                'usd_display_only' => true,
            ],
            'ticket_groups' => $ticketGroups->values()->all(),
            'tickets' => $tickets->map(fn (Ticket $ticket) => $this->mapTicket($ticket))->values()->all(),
        ];
    }

    private function groupTickets(Collection $tickets): Collection
    {
        return $tickets
            ->groupBy(fn (Ticket $ticket) => $this->groupCode($ticket))
            ->map(function (Collection $groupTickets) {
                $firstTicket = $groupTickets->first();
                $groupMeta = (array) data_get($firstTicket->metadata, 'group', []);

                return [
                    'code' => $this->groupCode($firstTicket),
                    'label_vi' => $groupMeta['label_vi'] ?? $firstTicket->type ?? $firstTicket->code,
                    'label_en' => $groupMeta['label_en'] ?? $groupMeta['label_vi'] ?? $firstTicket->type ?? $firstTicket->code,
                    'allow_none' => (bool) data_get($groupMeta, 'allow_none', true),
                    'none_label_vi' => data_get($groupMeta, 'none_label_vi', 'Không tham gia'),
                    'none_label_en' => data_get($groupMeta, 'none_label_en', 'None'),
                    'select_limit' => 1,
                    'tickets' => $groupTickets->map(fn (Ticket $ticket) => $this->mapTicket($ticket))->values()->all(),
                ];
            })
            ->sortBy(function (array $group) use ($tickets) {
                $groupTicket = $tickets->firstWhere('code', data_get($group, 'tickets.0.code'));

                return $groupTicket?->sort_order ?? 0;
            })
            ->values();
    }

    private function mapTicket(Ticket $ticket): array
    {
        $metadata = (array) ($ticket->metadata ?? []);
        $display = (array) data_get($metadata, 'display', []);
        $group = (array) data_get($metadata, 'group', []);
        $rules = (array) data_get($metadata, 'rules', []);

        $priceVnd = (int) round((float) $ticket->price);
        $priceUsd = data_get($display, 'price_usd');
        $priceUsd = $priceUsd === null || $priceUsd === '' ? null : (float) $priceUsd;
        $nameVi = (string) data_get($display, 'name_vi', $ticket->name);
        $nameEn = (string) data_get($display, 'name_en', $ticket->name);

        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'type' => $ticket->type,
            'group_code' => data_get($group, 'code', $ticket->type),
            'name' => $ticket->name,
            'name_vi' => $nameVi,
            'name_en' => $nameEn,
            'display_name' => $this->composeDisplayName($nameVi, $nameEn),
            'price_vnd' => $priceVnd,
            'price_vnd_display' => 'VND ' . number_format($priceVnd, 0, ',', '.'),
            'price_usd' => $priceUsd,
            'price_usd_display' => $priceUsd !== null ? 'USD ' . rtrim(rtrim(number_format($priceUsd, 2, '.', ''), '0'), '.') : null,
            'dates_string' => $ticket->dates_string,
            'dates_valid' => $ticket->dates_valid,
            'sort_order' => $ticket->sort_order,
            'max_quantity' => (int) data_get($rules, 'max_quantity', 1),
        ];
    }

    private function groupCode(Ticket $ticket): string
    {
        return (string) data_get($ticket->metadata, 'group.code', $ticket->type ?: $ticket->code);
    }

    private function composeDisplayName(string $nameVi, string $nameEn): string
    {
        if ($nameVi && $nameEn && $nameVi !== $nameEn) {
            return trim($nameVi . ' / ' . $nameEn);
        }

        return $nameVi ?: $nameEn;
    }
}
