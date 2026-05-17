@php
    $ticketHistory = $ticketHistory ?? [];
    $summary = $ticketHistory['summary'] ?? [];
    $ticketLines = collect($ticketHistory['ticket_lines'] ?? []);
    $orders = collect($ticketHistory['orders'] ?? []);
    $totalTickets = $ticketLines->sum('quantity');
    $summaryText = $ticketLines->take(2)->map(function (array $line) {
        return "{$line['display_name']} x{$line['quantity']}";
    })->implode(' • ');
@endphp

@if ($orders->isEmpty() && $ticketLines->isEmpty())
    <span class="text-muted">Chưa mua vé</span>
@else
    <div class="small lh-sm">
        <div class="d-flex flex-wrap gap-1 mb-1">
            <span class="badge bg-primary">
                {{ $summary['total_orders'] ?? $orders->count() }} đơn
            </span>
            <span class="badge bg-success">
                {{ $totalTickets }} vé
            </span>
            @if (!empty($summary['formatted_gross_revenue']))
                <span class="badge bg-secondary">
                    {{ $summary['formatted_gross_revenue'] }}
                </span>
            @endif
        </div>
        @if (!empty($summaryText))
            <div class="text-muted text-wrap">
                {{ $summaryText }}
                @if ($ticketLines->count() > 2)
                    <span class="text-secondary">...</span>
                @endif
            </div>
        @endif
    </div>
@endif
