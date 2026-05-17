<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/* customize */
/* hidec-vn */
class ClientsTicketsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $event;

    /**
     * @param int|null $lastColumnValue  If not null, this constant is used for the last column (your example uses 2).
     *                                    If null, the last column will be the ticket index (1..n).
     * @param array<int>|null $onlyClientIds  Optionally restrict to certain clients.
     * @param bool $dedupeBooths  If true, unique() the booths array before counting.
     */
    public function __construct(
        Event $event,
        protected ?int $lastColumnValue = 2,
        protected ?array $onlyClientIds = null,
        protected bool $dedupeBooths = true
    ) {
        $this->event = $event;
    }

    public function headings(): array
    {
        // Adjust labels as you like
        return ['#', 'QRCODE', 'NAME', 'EMAIL', 'PHONE', 'BOOTHS'];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $seq  = 1;

        $query = Client::query()
            ->select(['id', 'qrcode', 'name', 'email', 'custom_fields'])
            ->where('event_id', $this->event->id)
            // ->when($this->onlyClientIds, fn($q) => $q->whereIn('id', $this->onlyClientIds))
            ->orderBy('id');

        // Chunk to keep memory low if you have many clients
        $query->chunkById(1000, function ($clients) use (&$rows, &$seq) {
            foreach ($clients as $client) {
                // dd($clients);
                // Read booths array (robust to stringified JSON)
                $booths = data_get($client->custom_fields, 'booths', []);

                if (is_string($booths)) {
                    $decoded = json_decode($booths, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $booths = $decoded;
                    }
                }

                if ($this->dedupeBooths && is_array($booths)) {
                    $booths = collect($booths)->unique()->values()->all();
                }

                // if ($client->email = "khanhtram.nguyenn@gmail.com") {
                //     dd($booths, $client, $clients);
                // }

                $boothCount = is_array($booths) ? count($booths) : (int) $booths;

                // Ticket rule: every 15 booths = 1 ticket, capped at 4, and 46–53 → 4 (ceil)
                $tickets = $this->ticketCount($boothCount);

                // One row per ticket; suffix QR from the 2nd row on
                for ($t = 1; $t <= $tickets; $t++) {
                    $qr = $client->qrcode . ($t > 1 ? "-{$t}" : '');
                    $rows->push([
                        $seq++,                 // #
                        $qr,                    // QR Code (with suffix from 2nd)
                        $client->name,          // Name
                        $client->email,          // Email
                        $client->custom_fields['phone'],          // Name
                        $client->custom_fields['booths'],          // Name
                        $this->lastColumnValue ?? $t, // Last column: constant 2 (default) or ticket index
                    ]);
                }
            }
        });

        return $rows;
    }

    private function ticketCount(int $boothCount): int
    {
        if ($boothCount < 15) return 0;
        if ($boothCount == 53) return 4;
        if ($boothCount >= 15 && $boothCount < 30) return 1;
        if ($boothCount >= 30 && $boothCount < 45) return 2;
        if ($boothCount >= 45 && $boothCount < 53) return 3;

        // ceil(booths / 15), max 4, min 0
        $tickets = (int) ceil($boothCount / 15);
        return max(0, min(4, $tickets));
    }
}
