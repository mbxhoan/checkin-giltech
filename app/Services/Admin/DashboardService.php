<?php
namespace App\Services\Admin;

use App\Models\Event;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use Carbon\Carbon;
use App\Models\Client;

class DashboardService extends BaseService
{
    public function __construct()
    {

    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function report()
    {
        return app(ReportService::class);
    }

    public function getProgressOnGoingEvents(array $attributes = [])
    {
        $attributes['status'] = [Event::STATUS_ACTIVE];

        return Cache::remember($this->dashboardCacheKey('ongoing-events', $attributes), now()->addSeconds(60), function () use ($attributes) {
            $events = $this->event()->getListByAttributes($attributes);

            $events = $events->filter(function ($event) {
                return Carbon::parse($event->to_date)->toDateString() >= now()->toDateString();
            });

            foreach ($events as $event) {
                $event = $event->getProgressOnGoing();
            }

            return $events;
        });
    }

    public function getProvinceEventData(array $attributes = [])
    {
        return Cache::remember($this->dashboardCacheKey('province-event-data', $attributes), now()->addSeconds(60), function () use ($attributes) {
            $topProvinces = DB::table('events');

            if (count($attributes)) {
                foreach ($attributes as $key => $value) {
                    $topProvinces = $topProvinces->where($key, $value);
                }
            }

            $topProvinces = $topProvinces->join('provinces', 'events.province_id', '=', 'provinces.id')
                ->select('provinces.name', DB::raw('count(*) as total_events'))
                ->groupBy('provinces.name')
                ->orderByDesc('total_events')
                ->limit(9)
                ->get();

            $otherProvincesTotal = DB::table('events');

            if (count($attributes)) {
                foreach ($attributes as $key => $value) {
                    $otherProvincesTotal = $otherProvincesTotal->where($key, $value);
                }
            }

            $otherProvincesTotal = $otherProvincesTotal->join('provinces', 'events.province_id', '=', 'provinces.id')
                ->whereNotIn('provinces.name', $topProvinces->pluck('name')->toArray())
                ->count();

            $provinceData = [];

            foreach ($topProvinces as $province) {
                $provinceData[] = [
                    'name' => $province->name,
                    'quantity' => $province->total_events,
                ];
            }

            if ($otherProvincesTotal > 0) {
                $provinceData[] = [
                    'name' => 'Khác...',
                    'quantity' => $otherProvincesTotal,
                ];
            }

            $totalQuantity = collect($provinceData)->sum('quantity');

            return [
                'provinceData' => $provinceData,
                'totalQuantity' => $totalQuantity,
            ];
        });
    }

    public function getEventClientData(array $attributes = [])
    {
        return Cache::remember($this->dashboardCacheKey('event-client-data', $attributes), now()->addSeconds(60), function () use ($attributes) {
            $topEvents = DB::table('clients');

            if (count($attributes)) {
                foreach ($attributes as $key => $value) {
                    $topEvents = $topEvents->where($key, $value);
                }
            }

            $topEvents = $topEvents->join('events', 'clients.event_id', '=', 'events.id')
                ->select(
                    'events.id',
                    'events.code',
                    'events.name',
                    DB::raw('count(*) as total_clients')
                )
                ->groupBy('events.id')
                ->orderByDesc('total_clients')
                ->limit(9)
                ->get();

            $otherEventsTotal = DB::table('clients');

            if (count($attributes)) {
                foreach ($attributes as $key => $value) {
                    $otherEventsTotal = $otherEventsTotal->where($key, $value);
                }
            }

            $otherEventsTotal = $otherEventsTotal->join('events', 'clients.event_id', '=', 'events.id')
                ->whereNotIn('events.id', $topEvents->pluck('id')->toArray())
                ->count();

            $eventData = [];
            foreach ($topEvents as $event) {
                $eventData[] = [
                    'id' => $event->id,
                    'code' => $event->code,
                    'name' => $event->name,
                    'quantity' => $event->total_clients,
                ];
            }

            if ($otherEventsTotal > 0) {
                $eventData[] = [
                    'name' => 'Khác...',
                    'quantity' => $otherEventsTotal,
                ];
            }

            $totalQuantity = collect($eventData)->sum('quantity');

            return [
                'clientEventData' => $eventData,
                'totalQuantity' => $totalQuantity,
            ];
        });
    }

    private function dashboardCacheKey(string $prefix, array $attributes = []): string
    {
        ksort($attributes);

        return 'dashboard:'.$prefix.':'.md5(json_encode($attributes));
    }
     // Tổng số client type theo sự kiện
    public function totalClientByType(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->selectRaw("COALESCE(NULLIF(type, ''), 'Khác') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();
    }
    // Tổng số khách tham dự đã checkin theo type sự kiện
    public function totalClientCheckedInByType(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('type')
            ->where('type', '!=', '')
           ->whereExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')
                    ->where('c.event_id', $event->id)
                    ->where('c.type', 'CHECKIN');
            })
            ->selectRaw("COALESCE(NULLIF(type, ''), 'Khác') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();
    }

    // Tổng số khách tham dự đã checkout theo type sự kiện
    public function totalClientCheckedOutByType(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('type')
            ->where('type', '!=', '')
           ->whereExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')
                    ->where('c.event_id', $event->id)
                    ->where('c.type', 'CHECKOUT');
            })
            ->selectRaw("COALESCE(NULLIF(type, ''), 'Khác') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();
    }
}
