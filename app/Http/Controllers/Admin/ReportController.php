<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\ReportDataTable;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Email;
use App\Models\Event;
use App\Services\Admin\ReportService;
use App\Services\Videc\TicketAnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    private readonly TicketAnalyticsService $ticketAnalyticsService;

    public function __construct(ReportService $service, TicketAnalyticsService $ticketAnalyticsService)
    {
        $this->service = $service;
        $this->ticketAnalyticsService = $ticketAnalyticsService;
    }

    /**
     * Show the application events index.
     */
    public function index(ReportDataTable $dataTable)
    {
        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        $total = $dataTable->getFilter();

        return $dataTable->render('admin.reports.index', [
            'companyArray'          => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'total'                 => $total->count(),
        ]);
    }

    public function report(Event $event)
    {
        $this->authorize('report', $event);

        $clients = $this->service->middleware_client()->getClientWithCheckins($event->code, 50);
        // $event->clientsWithCheckins = $clients;
        $showTicketSummary = $event->code === 'videc-2026';
        $dataCheckins = $showTicketSummary ? [] : $this->service->getDataCheckin($event);
        // $dataChecked = $this->service->getDataChecked($event);
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedInCount($event->code);
        $ticketHistories = $showTicketSummary
            ? $this->ticketAnalyticsService->clientSummaries($this->clientCollection($clients))
            : [];

        $datas = [
            'event'             => $event,
            'clients'           => $clients,
            'totalCheckedIn'    => $totalCheckedIn,
            'videcAnalytics'    => $showTicketSummary ? $this->ticketAnalyticsService->forEvent($event) : null,
            'ticketHistories'   => $ticketHistories,
            'showTicketSummary' => $showTicketSummary,
            'checked'           => $this->clientCollection($clients)->filter(function ($client) {
                return !empty($client->checkins) && $client->checkins->count();
            })
        ];

        $datas = array_merge($datas, $dataCheckins);
        // $datas = array_merge($datas, $dataChecked);

        /* customize */
        /* sunhouse */
        if ($event->code === 'sunhouse') {
            $datas = array_merge($datas, $this->service->getReportSunhouse($event));
        }

        /* galaxy-holding */
        if (!$showTicketSummary) {
            $datas = array_merge($datas, $this->service->getGalaxyData($event));
        }

        /* email history */
        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);
        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());
        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'status'   => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_NEW,
                ],
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }
        if (!$showTicketSummary) {
            $datas = array_merge($datas, $this->service->getReportEmail($event));
            $datas = array_merge($datas, [
                'clientTypes'       => $types,
            ]);
        }
        /* end */

        return view('admin.reports.report', $datas);
    }

    public function getClientTable(Event $event)
    {
        // $this->authorize();
    }

    public function renderReport(Event $event)
    {
        $this->authorize('report', $event);

        $clients = $this->service->middleware_client()->getClientWithCheckins($event->code, 50);
        // $event->clientsWithCheckins = $clients;
        $showTicketSummary = $event->code === 'videc-2026';
        $dataCheckins = $showTicketSummary ? [] : $this->service->getDataCheckin($event);
        // $dataChecked = $this->service->getDataChecked($event);
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedInCount($event->code);
        $ticketHistories = $showTicketSummary
            ? $this->ticketAnalyticsService->clientSummaries($this->clientCollection($clients))
            : [];

        $datas = [
            'event'             => $event,
            'clients'           => $clients,
            'totalCheckedIn'    => $totalCheckedIn,
            'videcAnalytics'    => $showTicketSummary ? $this->ticketAnalyticsService->forEvent($event) : null,
            'ticketHistories'   => $ticketHistories,
            'showTicketSummary' => $showTicketSummary,
            'checked'           => $this->clientCollection($clients)->filter(function ($client) {
                return !empty($client->checkins) && $client->checkins->count();
            })
        ];

        $datas = array_merge($datas, $dataCheckins);
        // $datas = array_merge($datas, $dataChecked);

        /* customize */
        /* sunhouse */
        if ($event->code === 'sunhouse') {
            $datas = array_merge($datas, $this->service->getReportSunhouse($event));
        }

        /* galaxy-holding */
        if (!$showTicketSummary) {
            $datas = array_merge($datas, $this->service->getGalaxyData($event));
        }

        /* email history */
        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);
        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());
        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'status'   => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_NEW,
                ],
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }
        if (!$showTicketSummary) {
            $datas = array_merge($datas, $this->service->getReportEmail($event));
            $datas = array_merge($datas, [
                'clientTypes'       => $types,
            ]);
        }
        /* end */

        $emailHtml = '';
        if (!$showTicketSummary) {
            $emailHtml = view('admin.reports._email', $datas)->render();
        }

        return $this->responseSuccess([
            'html'  => view('admin.reports._report', $datas)->render(),
            'html2' => $emailHtml,
        ]);
    }

    private function clientCollection($clients): Collection
    {
        if (method_exists($clients, 'getCollection')) {
            return $clients->getCollection();
        }

        return collect($clients);
    }
}
