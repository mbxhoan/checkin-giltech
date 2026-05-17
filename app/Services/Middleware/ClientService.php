<?php
namespace App\Services\Middleware;

use App\Jobs\GenerateImageQrcode;
use App\Models\Checkin;
use App\Services\BaseService;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ClientService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Client::class);
    }

    public function checkin()
    {
        return app(CheckinService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
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

    public function ensureLimited(int $eventId, string $field)
    {
        $event = $this->event()->findById($eventId);
        $company = $event->company;

        if (isset($company->$field) && $company->$field > 0) {
            $list = $this->getListByAttributes([
                'event_id' => $eventId,
            ]);

            if (!empty($list) && $list->count() >= $company->$field) {
                return false;
            }
        }

        return true;
    }

    public function getClientCheckedIn(string $eventCode, array $attributes = [], $type = Checkin::TYPE_CHECKIN, $filters = [])
    {
        $query = DB::table('clients')
            ->where('clients.event_code', $eventCode);

        if (count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query = $query->where("clients.{$key}", $value);
            }
        }

        $query = $query->whereExists(function ($subquery) use ($type) {
                $subquery->select(DB::raw(1))
                            ->from('checkins')
                            ->whereRaw('clients.event_code = checkins.event_code')
                            ->whereRaw('clients.qrcode = checkins.qrcode')
                            ->where('checkins.status', '!=', Checkin::STATUS_DELETED)
                            ->where('checkins.type', $type);
        });

        return $query->get();

        $query = DB::table('checkins')
            // ->selectRaw('checkins.qrcode, count(*) as total_client_checkin')
            // ->groupBy('checkins.qrcode')
            ->where('checkins.status', '!=', Checkin::STATUS_DELETED)
            ->join('clients', function ($join) {
                $join->on('checkins.qrcode', '=', 'clients.qrcode')
                    ->on('checkins.event_code', '=', 'clients.event_code');
            })
            ->select(
                'clients.*',
                'checkins.user_id',
                // 'checkins.device_name',
                'checkins.client_name',
                'checkins.scan_time',
                'checkins.custom_fields as checkin_custom_fields',
                'checkins.type as checkin_type',
            )
            ->where('clients.event_code', $eventCode);

        if (!empty($status)) {
            $query->where([
                'checkins.status' => $status
            ]);
        }

        if (!empty($type)) {
            $query->where([
                'checkins.type' => $type
            ]);
        }

        if (!empty($filters['register_source'])) {
            $query->where([
                'clients.register_source' => $filters['register_source']
            ]);
        }

        if (!empty($filters['type'])) {
            $query->where([
                'clients.type' => $filters['type']
            ]);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('clients.created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('clients.created_at', '<=', $filters['to_date']);
        }

        // if (!empty($filters['username'])) {
        //     $query->where('checkins.device_name', '=', $filters['username']);
        // }

        if (!empty($filters['user_id'])) {
            $query->where('checkins.user_id', '=', $filters['user_id']);
        }

        $query->orderBy('checkins.updated_at', 'DESC');
        return $query->get();
    }

    public function getClientCheckedInCount(string $eventCode, array $attributes = [])
    {
        $query = DB::table('clients')
            ->where('clients.event_code', $eventCode);

        if (count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query = $query->where("clients.{$key}", $value);
            }
        }

        $query = $query->whereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
                ->from('checkins')
                ->whereRaw('clients.event_code = checkins.event_code')
                ->whereRaw('clients.qrcode = checkins.qrcode')
                ->where('checkins.status', '!=', Checkin::STATUS_DELETED)
                ->where('checkins.type', Checkin::TYPE_CHECKIN);
        });

        return $query->count();
    }

    public function getClientWithCheckins($eventCode, $paginate = 0, $status = null, $type = Checkin::TYPE_CHECKIN, $filters = [])
    {
        $query = Client::with(['checkins' => function ($query) use ($eventCode) {
            $query->where('checkins.event_code', $eventCode);
        }])
            ->where('event_code', $eventCode)
            ->where('status', '!=', Client::STATUS_DELETED);

        $query->orderBy('updated_at', 'DESC');

        if ($paginate > 0) {
            return $query->paginate($paginate);
        }

        return $query->get();

        $query = DB::table('clients')
            ->leftJoin('checkins', function ($join) {
                $join->on('clients.qrcode', '=', 'checkins.qrcode')
                    ->on('clients.event_code', '=', 'checkins.event_code');
            })
            ->where('clients.event_code', $eventCode)
            ->select(
                'clients.*',
                'checkins.id as checkin_id',
                'checkins.user_id',
                'checkins.scan_time',
                'checkins.custom_fields as checkin_custom_fields',
                'checkins.type as checkin_type',
            )
            ->get();

        return $query;

        $query = DB::table('clients')
            // ->selectRaw('checkins.qrcode, count(*) as total_client_checkin')
            // ->groupBy('checkins.qrcode')
            ->where('clients.status', '!=', Client::STATUS_DELETED)
            ->join('checkins', function ($join) {
                $join->on('checkins.qrcode', '=', 'checkins.qrcode')
                    ->on('checkins.event_code', '=', 'checkins.event_code');
            })
            ->select(
                'clients.*',
                'checkins.user_id',
                // 'checkins.device_name',
                'checkins.client_name',
                'checkins.scan_time',
                'checkins.custom_fields as checkin_custom_fields',
                'checkins.type as checkin_type',
            )
            ->where('clients.event_code', $eventCode);

        if (!empty($status)) {
            $query->where([
                'checkins.status' => $status
            ]);
        }

        if (!empty($type)) {
            $query->where([
                'checkins.type' => $type
            ]);
        }

        if (!empty($filters['register_source'])) {
            $query->where([
                'clients.register_source' => $filters['register_source']
            ]);
        }

        if (!empty($filters['type'])) {
            $query->where([
                'clients.type' => $filters['type']
            ]);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('clients.created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('clients.created_at', '<=', $filters['to_date']);
        }

        // if (!empty($filters['username'])) {
        //     $query->where('checkins.device_name', '=', $filters['username']);
        // }

        if (!empty($filters['user_id'])) {
            $query->where('checkins.user_id', '=', $filters['user_id']);
        }

        $query->orderBy('checkins.updated_at', 'DESC');
        return $query->get();
    }

    public function generateQrcode(string $eventCode, string $qrcode, bool $isJob = false)
    {
        $command = "generate:image-qrcode {$eventCode} --qrcode={$qrcode}";

        // if ($isJob) {
        //     $objJob = new GenerateImageQrcode($eventCode, $qrcode);
        //     $objJob->timeout = 600;
        //     $generateImageQrcodeJob = $objJob->delay(Carbon::now()->addSeconds(1));
        //     dispatch($generateImageQrcodeJob);
        //     return true;
        // }

        Artisan::call($command);
        return true;
    }

    public function countClientByCheck(string $eventCode, string $type) 
    {
        return DB::table('clients')
            ->where('clients.event_code', $eventCode)
            ->whereExists(function ($subquery) use ($type) {
                $subquery->select(DB::raw(1))
                    ->from('checkins')
                    ->whereRaw('clients.event_code = checkins.event_code')
                    ->whereRaw('clients.qrcode = checkins.qrcode')
                    ->where('checkins.type', $type);
            })
            ->count();
    }
}
