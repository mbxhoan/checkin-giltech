<?php

namespace App\Http\Controllers\Api\Videc;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Videc\TicketCatalogService;

class TicketCatalogController extends Controller
{
    public function __construct(private readonly TicketCatalogService $ticketCatalogService)
    {
    }

    public function show(Event $event)
    {
        return $this->responseSuccess(
            $this->ticketCatalogService->forEvent($event),
            'Ticket catalog'
        );
    }
}
