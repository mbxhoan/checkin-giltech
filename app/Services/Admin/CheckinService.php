<?php
namespace App\Services\Admin;

use App\Services\BaseService;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSetting;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use App\Services\Middleware\EmailService as MiddlewareEmailService;
use Carbon\Carbon;

class CheckinService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Checkin::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function audio()
    {
        return app(AudioService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function checkin()
    {
        $checkin = new MiddlewareCheckinService(
            $this->attributes['event_code'],
            $this->attributes['qrcode'],
            $this->attributes['scan_time'] ?? now()->format('Y-m-d H:i:s'),
        );

        $checkin->attributes = [
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP,
            'type'          => $this->attributes['type'] ?? null,
        ];

        return $checkin->checkin();
    }

    public function applyFilters(int $eventId, $query)
    {
        $query = $query->select(
                'checkins.*',
                'clients.country_id',
                'clients.email',
                'clients.type as client_type',
                'clients.register_source',
                'clients.custom_fields as client_custom_fields',
                'clients.status as client_status',
                'clients.created_by as client_created_by',
                'clients.updated_by as client_updated_by',
            )
            ->join('clients', 'checkins.qrcode', '=', 'clients.qrcode')
            ->where('clients.event_id', $eventId);

        $query = $this->applyRequestFilters($query);

        if (request()->boolean('unique_qrcode')) {
            $uniqueQuery = $query->getQuery()
                ->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                ->cloneWithoutBindings(['order']);

            $uniqueQuery->selectRaw('MAX(checkins.id) as id')
                ->groupBy('checkins.qrcode');

            $query->whereIn('checkins.id', $uniqueQuery);
        }

        return $query;
    }

    private function applyRequestFilters($query)
    {
        if (request()->filled('scan_type')) {
            $scanType = request()->input('scan_type');

            if ($scanType == 'empty') {
                $query->where(function ($q) {
                    $q->whereNull('clients.type')
                        ->orWhere('clients.type', '');
                });
            } else {
                $query->where('clients.type', $scanType);
            }
        }

        if (request()->filled('status')) {
            $attributes['status'] = request()->input('status');
        }

        if (request()->filled('type')) {
            $attributes['type'] = request()->input('type');
        }

        if (request()->filled('register_source')) {
            $attributes['register_source'] = request()->input('register_source');
        }

        if (request()->filled('field_date') && request()->filled('from_date') && request()->filled('to_date')) {
            $dateTimes[request()->input('field_date')] = [
                request()->input('from_date'),
                request()->input('to_date'),
            ];
        }

        if (isset($attributes) && count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query->where("checkins.{$key}", $value);
            }
        }

        if (isset($dateTimes) && count($dateTimes)) {
            foreach ($dateTimes as $key => $value) {
                if (in_array($key, [
                    'scan_time'
                ])) {
                    $query->whereBetween("checkins.{$key}", [
                        Carbon::parse($value[0])->startOfDay(),
                        Carbon::parse($value[1])->endOfDay()
                    ]);
                } else {
                    $query->whereBetween("checkins.{$key}", $value);
                }
            }
        }

        return $query;
    }

    public function destroyAll(Event $event, ?string $qrcode = null)
    {
        $date = now()->format('Ymd_His');
        $fileName = "{$event->code}_{$date}.json";
        $query = $this->getQuery()
            ->where('checkins.event_id', $event->id);
            // ->where('checkins.status', '!=', Checkin::STATUS_DELETED);

        if ($qrcode) {
            $query->where('checkins.qrcode', $qrcode);
            $fileName = "{$event->code}_{$qrcode}_{$date}.json";
        }

        $query = $this->applyFilters($event->id, $query);
        $count = $query->count();
        $checkins = $query->get();

        if ($count == 0) {
            return [
                'status'    => true,
                'count'     => $count,
            ];
        }

        if ($this->convertToJsonFile(json_decode($checkins, true), "backups/checkins", $fileName)) {
            foreach ($checkins as $checkin) {
                $this->destroy($checkin);
            }
        } else {
            return [
                'status'    => false,
                'count'     => 0,
            ];
        }

        return [
            'status'    => true,
            'count'     => $count,
        ];
    }

    public function destroy(Checkin $checkin)
    {
        return $this->delete($checkin->id);
    }
}
