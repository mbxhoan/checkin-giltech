<?php
namespace App\Services\Admin;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Email;
use App\Models\Event;
use App\Services\BaseService;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;

class ReportService extends BaseService
{
    public function __construct()
    {

    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function province()
    {
        return app(ProvinceService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function event_file()
    {
        return app(EventFileService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function postmark()
    {
        return app(PostmarkService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    /* customize */
    /* sunhouse */
    public function getReportSunhouse(Event $event)
    {
        $sunhouse = $this->getRedis("report_sunhouse", $event->code, "array");

        if (!count($sunhouse)) {
            $sunhouse = [];

            $clients = $event->clientsWithCheckins ?? null;
            if (empty($clients)) $clients = $this->middleware_client()->getClientWithCheckins($event->code);

            foreach ($clients as $client) {
                $customFields = is_string($client->custom_fields)
                    ? json_decode($client->custom_fields, true)
                    : $client->custom_fields;

                foreach ([
                    'tang',
                    'hang',
                    'kenh',
                    'mien',
                ] as $field) {
                    $value = $customFields[$field] ?? null;
                    $value = trim($value);

                    if ($value !== null) {
                        $sunhouse[$field][$value]['total'] = ($sunhouse[$field][$value]['total'] ?? 0) + 1;
                    }

                    if (!empty($client->checkins) && $client->checkins->count()) {
                        if ($value !== null) {
                            $sunhouse[$field][$value]['count'] = ($sunhouse[$field][$value]['count'] ?? 0) + 1;
                        }
                    }
                }

                /* type */
                $value = $client->type ?? null;
                $value = trim($value);
                $sunhouse['type'][$value]['total'] = ($sunhouse['type'][$value]['total'] ?? 0) + 1;
                if (!empty($client->checkins) && $client->checkins->count()) {
                    if ($value !== null) {
                        $sunhouse['type'][$value]['count'] = ($sunhouse['type'][$value]['count'] ?? 0) + 1;
                    }
                }
            }

            /* sorting by count */
            // foreach ($sunhouse as $field => $levels) {
            //     uasort($sunhouse[$field], function ($a, $b) {
            //         return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            //     });
            // }

            /* sorting by value */
            foreach ([
                'tang',
                'hang'
            ] as $field) {
                if (!empty($sunhouse[$field])) {
                    ksort($sunhouse[$field], SORT_NATURAL); // or SORT_NUMERIC if all values are numeric
                }
            }

            $this->updateRedis("report_sunhouse", $event->code, json_encode($sunhouse), config("app.times.seconds.one-minute"));
            $sunhouse = $this->getRedis("report_sunhouse", $event->code, "array");
        }

        return [
            'sunhouse'   => $sunhouse
        ];
    }

    /* galaxy-holding */
    public function getGalaxyData(Event $event)
    {
        $galaxy = $this->getRedis("report_galaxy", $event->code, "array");
        if (!is_array($galaxy)) {
            $galaxy = [];
        }

        if (!count($galaxy)) {
            $galaxy = [];
            $clients = $event->clientsWithCheckins ?? null;
            if (empty($clients)) $clients = $this->middleware_client()->getClientWithCheckins($event->code);

            foreach ($event->areas as $area) {
                $userIds = $area->users->pluck('id')->toArray();
                $areas[$area->id] = $userIds;
            }

            foreach ($clients as $client) {
                foreach ($event->areas as $area) {
                    // $galaxy[$area->name]['total'] = ($galaxy[$area->name][$value]['total'] ?? 0) + 1;

                    if (!empty($client->checkins) && $client->checkins->count()) {
                        foreach ($client->checkins as $checkin) {
                            if (in_array($checkin->user_id, $areas[$area->id])) {
                                $galaxy[$area->name]['count'] = ($galaxy[$area->name]['count'] ?? 0) + 1;
                            }
                        }

                    }
                }

                /* type */
                $value = $client->type ?? ' - ';
                $value = trim($value);
                $galaxy['type'][$value]['total'] = ($galaxy['type'][$value]['total'] ?? 0) + 1;
                if (!empty($client->checkins) && $client->checkins->count()) {
                    $galaxy['type'][$value]['count'] = ($galaxy['type'][$value]['count'] ?? 0) + 1;
                }
            }

            $this->updateRedis("report_galaxy", $event->code, json_encode($galaxy), config("app.times.seconds.one-minute"));
            $galaxy = $this->getRedis("report_galaxy", $event->code, "array");
        }

        return [
            'galaxy'   => $galaxy
        ];
    }

    public function getDataChecked(Event $event)
    {
        $checked = $this->getRedis("report_checked", $event->code, "array");

        if (!count($checked)) {
            $checked = $this->middleware_client()->getClientCheckedIn($event->code);
            $this->updateRedis("report_checked", $event->code, json_encode($checked), config("app.times.seconds.one-minute"));
            $checked = $this->getRedis("report_checked", $event->code, "array");
        }

        return [
            'checked'   => $checked
        ];
    }

    public function getDataCheckin(Event $event)
    {
        $checkins = $this->getRedis("report_checked_in_client_by_date_time", $event->code, "array");
        if (!is_array($checkins)) {
            $checkins = [];
        }

        if (!count($checkins)) {
            $checkins = $this->getCheckedInClientByDateTime($event, $event->from_date, $event->to_date, false);
            $this->updateRedis("report_checked_in_client_by_date_time", $event->code, json_encode($checkins), config("app.times.seconds.one-minute"));
            $checkins = $this->getRedis("report_checked_in_client_by_date_time", $event->code, "array");
        }

        $dateTimes = $this->getRedis("report_date_time_value", $event->code, "array");
        if (!is_array($dateTimes)) {
            $dateTimes = [];
        }

        if (!count($dateTimes)) {
            $dateTimes = $this->getDateTimeValue($event->from_date, $event->to_date, false, 1);
            $this->updateRedis("report_date_time_value", $event->code, json_encode($dateTimes), config("app.times.seconds.five-minutes"));
            $dateTimes = $this->getRedis("report_date_time_value", $event->code, "array");
        }

        return [
            'checkins'  => $checkins,
            'dateTimes' => $dateTimes,
        ];
    }

    public function getCheckedInClientByDateTime(Event $event, string $fromDate, string $toDate, bool $displayToTime = true)
    {
        $dataOnTime = [];
        $dateTimes = $this->getDateTimeValue($fromDate, $toDate, false, 2);

        foreach ($dateTimes as $date => $times) {
            $totalCheckedInOnDate[$date] = 0;

            foreach ($times as $keyTime => $time) {
                // dd($times, $keyTime, $time);
                $nextTime = next($times); // Get the next item

                if ($nextTime !== false) {
                    $betweenHours = [
                        'from_time' => "{$date} {$keyTime}",
                        'to_time'   => "{$date} {$nextTime}",
                    ];

                    $checkedInClientBetweenTime = $this->getCheckinList($event->code, null, false, null, [], $betweenHours);
                    $key = $keyTime;

                    if ($displayToTime) {
                        $key = "{$keyTime} - {$nextTime}";
                    }

                    // $dataOnTime[$date][$key] = $checkedInClientBetweenTime;
                    $dataOnTime[$date][$key] = $checkedInClientBetweenTime->count();
                    $totalCheckedInOnDate[$date] += $checkedInClientBetweenTime->count();
                } else {
                    unset($dateTimes[$date][$keyTime]);
                }
            }

            $dataOnTime["{$date} ($totalCheckedInOnDate[$date])"] = $dataOnTime[$date];
            unset($dataOnTime[$date]);
        }

        return $dataOnTime;
    }

    public function getCheckinList(string $eventCode, $source = null, $groupByUser = false, $userName = null, $filters = [], $betweenHours = [])
    {
        $groupBy = [
            'checkins.qrcode'
        ];

        if ($groupByUser) {
            $groupBy = [
                'checkins.qrcode',
                'checkins.device_name',
            ];
        }

        $query = DB::table('checkins as checkins')
            /* chỉ lấy qrcode */
            ->select('checkins.qrcode')
            ->orderBy('checkins.qrcode', 'ASC')
            ->join('clients as clients', function ($join) use ($eventCode) {
                $join->on('checkins.qrcode', '=', 'clients.qrcode')
                    ->where('checkins.event_code', '=', $eventCode)
                    ->where('clients.status', '!=', Client::STATUS_DELETED)
                    ->where('checkins.status', '!=', Checkin::STATUS_DELETED);
            })
            ->groupBy($groupBy);

        if (!empty($source)) {
            $query = $query->where([
                'clients.register_source' => $source
            ]);
        }

        if (!empty($userName)) {
            $query = $query->where([
                'checkins.device_name' => $userName
            ]);
        }

        if (!empty($filters)) {
            if (!empty($filters['from_date'])) {
                $query = $query->whereDate('checkins.scan_time', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $query = $query->whereDate('checkins.scan_time', '<=', $filters['to_date']);
            }
        }

        if (!empty($betweenHours)) {
            if (!empty($betweenHours['from_time']) && !empty($betweenHours['to_time'])) {
                $query = $query->whereBetween('checkins.scan_time', [
                    $betweenHours['from_time'],
                    $betweenHours['to_time']
                ]);
            }
        }

        return $query->get();
    }

    public function getDateTimeValue($fromDate, $toDate, $hourMinutesOnly = false, $additionalTime = 2)
    {
        $fromDate = new DateTime($fromDate); // Replace with your start date
        $toDate = new DateTime($toDate);   // Replace with your end date
        $toDate = $toDate->modify('+ 1 day');

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($fromDate, $interval, $toDate);

        $dataOnTime = [];

        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            $hours = [];

            $startTime = new DateTime($currentDate . ' 08:00:00');
            $endTime = new DateTime($currentDate . ' 21:00:00');
            $endTime = $endTime->modify("+ {$additionalTime} hour");
            $interval = new DateInterval('PT1H'); // 1 hour interval

            $hourRange = new DatePeriod($startTime, $interval, $endTime);

            foreach ($hourRange as $hour) {
                $key = $hour->format('H:i:s');
                /* chưa đổi được */
                // $next = next($hourRange);
                // $key = "{$key} - {$next}";

                if ($hourMinutesOnly) {
                    $key = $hour->format('H:i');
                }

                $hours[$key] = $hour->format('H:i:s');
            }

            $dataOnTime[$currentDate] = $hours;
        }

        return $dataOnTime;
    }

    public function getReportEmail(Event $event)
    {
        $campaignIds = $event->campaigns()->pluck('id');
        $perPage = (int) request('email_page_size', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        }
        $perPage = min($perPage, 200);

        $baseQuery = Email::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereIn('status', [
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
                Email::STATUS_CLOSED,
            ]);

        // Optional filter by campaign_id from request
        if (request()->filled('campaign_id')) {
            $baseQuery->where('campaign_id', request('campaign_id'));
        }

        // Optional filter by clients.type from request->types
        if (request()->filled('types')) {
            $types = (array) request('types'); // ensure array
            $baseQuery->whereHas('client', function ($q) use ($types) {
                $q->whereIn('type', $types);
            });
        }

        $emails = (clone $baseQuery)
            ->with([
                'webhookPostmarks' => function ($query) {
                    $query->orderBy('record_time', 'ASC');
                },
            ])
            ->orderBy('status', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($perPage, ['*'], 'email_page')
            ->withQueryString();

        $statusQuery = DB::table('webhook_postmarks')
            ->join('emails', 'emails.message_id', '=', 'webhook_postmarks.message_id')
            ->whereIn('emails.campaign_id', $campaignIds)
            ->whereIn('emails.status', [
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
                Email::STATUS_CLOSED,
            ])
            ->whereNotNull('emails.sent_at')
            ->where('webhook_postmarks.status', '!=', 'SubscriptionChange');

        if (request()->filled('campaign_id')) {
            $statusQuery->where('emails.campaign_id', request('campaign_id'));
        }

        if (request()->filled('types')) {
            $types = (array) request('types');
            $statusQuery->join('clients', 'clients.qrcode', '=', 'emails.qrcode')
                ->whereIn('clients.type', $types);
        }

        $dataStatuses = $statusQuery
            ->select('webhook_postmarks.status', DB::raw('count(distinct webhook_postmarks.email) as total'))
            ->groupBy('webhook_postmarks.status')
            ->pluck('total', 'webhook_postmarks.status')
            ->toArray();

        $hooksFilter = [
            'webhook_postmarks.status' => 'Delivery',
        ];

        /* customize */
        /* galaxy-holding */
        if ($event->code === 'galaxy-holding') {
            $hooksFilter = [
                'webhook_postmarks.status'  => 'Delivery',
                'webhook_postmarks.tag'     => [
                    "Hackathon - Thí sinh",
                    "Hackathon - Mentor",
                    'Connecting Day - Premium',
                    "Connecting Day - VIP",
                ]
            ];
        }

        $hooksQuery = DB::table('webhook_postmarks')
            ->join('emails', 'emails.message_id', '=', 'webhook_postmarks.message_id')
            ->whereIn('emails.campaign_id', $campaignIds)
            ->whereNotNull('emails.sent_at')
            ->whereIn('emails.status', [
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
                Email::STATUS_CLOSED,
            ]);

        if (request()->filled('campaign_id')) {
            $hooksQuery->where('emails.campaign_id', request('campaign_id'));
        }

        if (request()->filled('types')) {
            $types = (array) request('types');
            $hooksQuery->join('clients', 'clients.qrcode', '=', 'emails.qrcode')
                ->whereIn('clients.type', $types);
        }

        foreach ($hooksFilter as $attrCol => $attrValue) {
            if (is_array($attrValue)) {
                $hooksQuery->whereIn($attrCol, $attrValue);
            } else {
                $hooksQuery->where($attrCol, $attrValue);
            }
        }

        $hooks = $hooksQuery
            ->selectRaw('webhook_postmarks.email, count(*) as total_webhook')
            ->groupBy('webhook_postmarks.email')
            ->orderBy('total_webhook', 'DESC')
            ->get();

        return [
            'emails'        => $emails,
            'dataStatuses'  => $dataStatuses ?? [],
            'hooks'         => $hooks,
        ];
    }
}
