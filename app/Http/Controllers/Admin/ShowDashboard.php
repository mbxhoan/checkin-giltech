<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Event;
use App\Models\Role;
use App\Services\Admin\DashboardService;
use App\Services\Videc\TicketAnalyticsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ShowDashboard extends Controller
{
    private readonly TicketAnalyticsService $ticketAnalyticsService;

    public function __construct(DashboardService $service, TicketAnalyticsService $ticketAnalyticsService)
    {
        $this->service = $service;
        $this->ticketAnalyticsService = $ticketAnalyticsService;
    }

    /**
     * Show the application admin dashboard.
     */
    public function __invoke(): View
    {
        $data = [];
        $view = 'admin.dashboard.index';
        $month = now()->month;
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        if (auth()->user()->isSysAdmin()) {
            $clientsQuery = $this->service->client()->getQueryByAttributes();
            $eventsQuery = $this->service->event()->getQueryByAttributes();
            $emailsQuery = $this->service->email()->getQueryByAttributes([], [
                'sent_at' => null,
            ]);
            $provinceEventData = $this->service->getProvinceEventData();
            $clientEventData = $this->service->getEventClientData();
            $events = (clone $eventsQuery)
                ->select(['id', 'name', 'from_date', 'to_date', 'code', 'company_id'])
                ->get();

            $clientsCount = (clone $clientsQuery)->count();
            $clientsThisMonthCount = (clone $clientsQuery)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $clientsRegisterLpCount = (clone $clientsQuery)
                ->where('register_source', Client::REGISTER_LP)
                ->count();
            $eventsThisMonthCount = (clone $eventsQuery)
                ->whereBetween('from_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->count();
            $emailsCount = (clone $emailsQuery)->count();
            $emailsThisMonthCount = (clone $emailsQuery)
                ->whereBetween('sent_at', [$monthStart, $monthEnd])
                ->count();

            $registers = [];
            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[] = (clone $clientsQuery)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('register_source', $key)
                    ->count();
            }

            $eventsOnGoing = $this->service->getProgressOnGoingEvents();
            $videcAnalytics = $this->videcAnalyticsForEvent(
                Event::query()->where('code', 'videc-2026')->first()
            );

            $data = [
                'month'             => $month,
                'events'            => $events,
                'eventsThisMonth'   => $eventsThisMonthCount,
                'eventsOnGoing'     => $eventsOnGoing,
                'clients'           => $clientsCount,
                'clientsx5'         => $this->recentClients(),
                'clientsRegisterLp' => $clientsRegisterLpCount,
                'clientsThisMonth'  => $clientsThisMonthCount,
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers,
                'landingPages'      => $this->service->landing_page()->getQueryByAttributes()->count(),
                'campaigns'         => $this->service->campaign()->getQueryByAttributes()->count(),
                'emails'            => $emailsCount,
                'emailsThisMonth'   => $emailsThisMonthCount,
                'provinceEventData' => $provinceEventData['provinceData'],
                'totalQuantity'     => $provinceEventData['totalQuantity'],
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
                'videcAnalytics'    => $videcAnalytics,
            ];

            $view = 'admin.dashboard.index';
        } else if (auth()->user()->isAdmin()) {
            $eventArray = auth()->user()->company->events->pluck('id', 'id')->toArray();
            $eventsQuery = $this->service->event()->getQueryByAttributes([
                'company_id'    => auth()->user()->company_id
            ]);
            $events = (clone $eventsQuery)
                ->select(['id', 'name', 'from_date', 'to_date', 'code', 'company_id'])
                ->get();
            $campaignsQuery = $this->service->campaign()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ]);
            $clientsQuery = $this->service->client()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ]);
            $emailsQuery = $this->service->email()->getQueryByAttributes([
                'campaign_id'   => $campaignsQuery
                    ->select('id')
                    ->pluck('id')
                    ->toArray()
            ], [
                'sent_at'       => null,
            ]);
            $provinceEventData = $this->service->getProvinceEventData([
                'company_id'    => auth()->user()->company_id
            ]);
            $clientEventData = $this->service->getEventClientData([
                'company_id'    => auth()->user()->company_id
            ]);

            $clientsCount = (clone $clientsQuery)->count();
            $clientsThisMonthCount = (clone $clientsQuery)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $clientsRegisterLpCount = (clone $clientsQuery)
                ->where('register_source', Client::REGISTER_LP)
                ->count();
            $campaignsCount = (clone $campaignsQuery)->count();
            $landingPagesCount = $this->service->landing_page()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ])->count();
            $emailsCount = (clone $emailsQuery)->count();
            $emailsThisMonthCount = (clone $emailsQuery)
                ->whereBetween('sent_at', [$monthStart, $monthEnd])
                ->count();
            $eventsThisMonthCount = (clone $eventsQuery)
                ->whereBetween('from_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->count();

            $registers = [];
            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[] = (clone $clientsQuery)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('register_source', $key)
                    ->count();
            }

            $eventsOnGoing = $this->service->getProgressOnGoingEvents([
                'company_id'    => auth()->user()->company_id
            ]);
            $videcAnalytics = $this->videcAnalyticsForEvent(
                Event::query()
                    ->where('company_id', auth()->user()->company_id)
                    ->where('code', 'videc-2026')
                    ->first()
            );

            $data = [
                'month'             => $month,
                'events'            => $events,
                'eventsThisMonth'   => $eventsThisMonthCount,
                'eventsOnGoing'     => $eventsOnGoing,
                'landingPages'      => $landingPagesCount,
                'campaigns'         => $campaignsCount,
                'clients'           => $clientsCount,
                'clientsx5'         => $this->recentClients([
                    'event_id'      => $eventArray,
                ]),
                'clientsRegisterLp' => $clientsRegisterLpCount,
                'clientsThisMonth'  => $clientsThisMonthCount,
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers,
                'emails'            => $emailsCount,
                'emailsThisMonth'   => $emailsThisMonthCount,
                'provinceEventData' => $provinceEventData['provinceData'],
                'totalQuantity'     => $provinceEventData['totalQuantity'],
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
                'videcAnalytics'    => $videcAnalytics,
            ];

            $view = 'admin.dashboard.index';
        } else if (auth()->user()->hasRole(Role::ROLE_USER)) {
            $eventArray = auth()->user()->company->events->where('id', auth()->user()->event_id)->pluck('id', 'id')->toArray();
            $event = $this->service->event()->findByAttributes([
                'id'            => auth()->user()->event_id
            ]);
            $campaignsQuery = $this->service->campaign()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ]);
            $clientsQuery = $this->service->client()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ]);
            $emailsQuery = $this->service->email()->getQueryByAttributes([
                'campaign_id'   => $campaignsQuery
                    ->select('id')
                    ->pluck('id')
                    ->toArray()
            ], [
                'sent_at'       => null,
            ]);
            $clientEventData = $this->service->getEventClientData([
                'event_id'    => auth()->user()->event_id
            ]);
            $videcAnalytics = $this->videcAnalyticsForEvent($event);

            $clientsCount = (clone $clientsQuery)->count();
            $clientsThisMonthCount = (clone $clientsQuery)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $clientsRegisterLpCount = (clone $clientsQuery)
                ->where('register_source', Client::REGISTER_LP)
                ->count();
            $campaignsCount = (clone $campaignsQuery)->count();
            $landingPagesCount = $this->service->landing_page()->getQueryByAttributes([
                'event_id'      => $eventArray,
            ])->count();
            $emailsCount = (clone $emailsQuery)->count();
            $emailsThisMonthCount = (clone $emailsQuery)
                ->whereBetween('sent_at', [$monthStart, $monthEnd])
                ->count();
            $registers = [];
            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[] = (clone $clientsQuery)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('register_source', $key)
                    ->count();
            }

            $dataCheckins = $this->service->report()->getDataCheckin($event);
            $dataChecked = $this->service->report()->getDataChecked($event);

            $registerByType = $this->service->totalClientByType($event)
                ->pluck('total', 'type')->toArray();
            $checkinByType = $this->service->totalClientCheckedInByType($event)
                ->pluck('total', 'type')->toArray();
            $checkoutByType = $this->service->totalClientCheckedOutByType($event)
                ->pluck('total', 'type')->toArray();

            $data = [
                'month'             => $month,
                'event'             => $event,
                'landingPages'      => $landingPagesCount,
                'campaigns'         => $campaignsCount,
                'clients'           => $clientsCount,
                'clientsx5'         => $this->recentClients([
                    'event_id'      => $eventArray,
                ]),
                'clientsRegisterLp' => $clientsRegisterLpCount,
                'clientsThisMonth'  => $clientsThisMonthCount,
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers,
                'emails'            => $emailsCount,
                'emailsThisMonth'   => $emailsThisMonthCount,
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
                'totalCheckedIn'    => $this->service->middleware_client()->getClientCheckedIn($event->code),
                'videcAnalytics'    => $videcAnalytics,
                
                'registerByType'        => $registerByType ?? null,
                'checkinByType'         => $checkinByType ?? null,
                'checkoutByType'        => $checkoutByType ?? null,
            ];

            $data = array_merge($data, $dataChecked);
            $data = array_merge($data, $dataCheckins);
            $view = 'admin.dashboard.index';
        }

        if (!auth()->user()->isAdmin() && auth()->user()->hasRole(Role::ROLE_SCANNER)) {
            $user = auth()->user();
            return view('scan.index', [
                'events' => $events ?? ($user->event_id
                    ? $user->company->events->where('id', $user->event_id)->values()
                    : $user->company->events),
            ]);
        }

        return view($view, $data);
    }

    private function videcAnalyticsForEvent(?Event $event): ?array
    {
        if (empty($event) || $event->code !== 'videc-2026') {
            return null;
        }

        return $this->ticketAnalyticsService->forEvent($event);
    }

    private function recentClients(array $attributes = [])
    {
        return $this->service->client()->getQueryByAttributes($attributes)
            ->select([
                'id',
                'qrcode',
                'name',
                'status',
                'updated_at',
                'updated_by',
            ])
            ->with(['user:id,name'])
            ->limit(5)
            ->get();
    }
}
