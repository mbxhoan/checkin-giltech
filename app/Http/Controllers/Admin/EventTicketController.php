<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tickets\TicketUpsertRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\Admin\EventService;
use App\Services\Videc\TicketCrudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTicketController extends Controller
{
    public function __construct(
        private readonly TicketCrudService $ticketCrudService,
        private readonly EventService $eventService
    ) {
    }

    public function selectEventIndex(): View
    {
        $events = $this->eventService->getEventList();

        return view('admin.tickets.index', [
            'eventArray' => $events->mapWithKeys(function ($event) {
                return [
                    $event->id => "{$event->code} - {$event->name}",
                ];
            })->toArray(),
        ]);
    }

    public function selectEventToManage(SelectEventToCreateRequest $request): RedirectResponse
    {
        return redirect()->route('admin.events.tickets.index', [
            'event' => $request->event_id,
        ]);
    }

    public function index(string $event, Request $request): View
    {
        $event = $this->resolveEvent($event);
        $this->authorize('edit', $event);

        $tickets = $this->ticketCrudService->listForEvent($event);
        $selectedTicket = $this->resolveSelectedTicket($tickets, $request->integer('ticket'));
        $formTicket = $selectedTicket ?: new Ticket([
            'event_code' => $event->code,
            'sort_order' => $this->ticketCrudService->nextSortOrder($event),
            'type' => 'conference',
        ]);

        return view('admin.events.tickets.index', [
            'event' => $event,
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'formValues' => array_merge(
                $this->ticketCrudService->formValues($formTicket),
                $this->oldTicketValues($formTicket)
            ),
        ]);
    }

    public function store(TicketUpsertRequest $request, string $event): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $this->authorize('edit', $event);

        $ticket = $this->ticketCrudService->upsert($event, $request->validated());

        return redirect()
            ->route('admin.events.tickets.index', [
                'event' => $event,
                'ticket' => $ticket->id,
            ])
            ->withSuccess('Tạo vé thành công');
    }

    public function update(TicketUpsertRequest $request, string $event, string $ticket): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $ticket = $this->resolveTicket($ticket);
        $this->authorize('edit', $event);

        $ticket = $this->ticketCrudService->upsert($event, $request->validated(), $ticket);

        return redirect()
            ->route('admin.events.tickets.index', [
                'event' => $event,
                'ticket' => $ticket->id,
            ])
            ->withSuccess('Cập nhật vé thành công');
    }

    public function destroy(string $event, string $ticket): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $ticket = $this->resolveTicket($ticket);
        $this->authorize('edit', $event);

        $this->ticketCrudService->delete($event, $ticket);

        return redirect()
            ->route('admin.events.tickets.index', $event)
            ->withSuccess('Xoá vé thành công');
    }

    private function resolveSelectedTicket($tickets, ?int $ticketId): ?Ticket
    {
        if (!$ticketId) {
            return null;
        }

        return $tickets->firstWhere('id', $ticketId);
    }

    private function oldTicketValues(Ticket $formTicket): array
    {
        return [
            'code' => old('code', $formTicket->code),
            'name' => old('name', $formTicket->name),
            'type' => old('type', $formTicket->type),
            'sort_order' => old('sort_order', $formTicket->sort_order),
            'price' => old('price', $formTicket->price),
            'dates_string' => old('dates_string', $formTicket->dates_string),
            'dates_valid_from' => old('dates_valid_from', data_get($formTicket->dates_valid, 'starts_at')),
            'dates_valid_to' => old('dates_valid_to', data_get($formTicket->dates_valid, 'ends_at')),
            'group_code' => old('group_code', data_get($formTicket->metadata, 'group.code')),
            'group_label_vi' => old('group_label_vi', data_get($formTicket->metadata, 'group.label_vi')),
            'group_label_en' => old('group_label_en', data_get($formTicket->metadata, 'group.label_en')),
            'group_allow_none' => old('group_allow_none', data_get($formTicket->metadata, 'group.allow_none', true)),
            'group_none_label_vi' => old('group_none_label_vi', data_get($formTicket->metadata, 'group.none_label_vi')),
            'group_none_label_en' => old('group_none_label_en', data_get($formTicket->metadata, 'group.none_label_en')),
            'display_name_vi' => old('display_name_vi', data_get($formTicket->metadata, 'display.name_vi')),
            'display_name_en' => old('display_name_en', data_get($formTicket->metadata, 'display.name_en')),
            'display_price_usd' => old('display_price_usd', data_get($formTicket->metadata, 'display.price_usd')),
            'max_quantity' => old('max_quantity', data_get($formTicket->metadata, 'rules.max_quantity', 1)),
            'metadata_json' => old(
                'metadata_json',
                $formTicket->isNew()
                    ? null
                    : json_encode((array) ($formTicket->metadata ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ),
        ];
    }

    private function resolveEvent(string $event): Event
    {
        return Event::query()->findOrFail($event);
    }

    private function resolveTicket(string $ticket): Ticket
    {
        return Ticket::query()->findOrFail($ticket);
    }
}
