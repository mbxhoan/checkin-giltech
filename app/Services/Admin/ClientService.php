<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\Jobs\GenerateImageQrcode;
use App\Jobs\ImportExcelClient;
use App\Models\Campaign;
use App\Models\Checkin;
use App\Services\BaseService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\Event;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use App\Services\Middleware\CardService as MiddlewareCardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CustomFieldTemplate;
use App\Models\Email;
use Illuminate\Database\Eloquent\Builder;

class ClientService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Client::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function card()
    {
        return app(CardService::class);
    }

    public function checkin()
    {
        return app(CheckinService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function label()
    {
        return app(LabelService::class);
    }

    public function label_detail()
    {
        return app(LabelDetailService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function client_backup()
    {
        return app(ClientBackupService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function middleware_card()
    {
        return app(MiddlewareCardService::class);
    }

    public function applyFilters($query)
    {
        /* lọc clients */
        if (request()->has('checked_in')) {
            $checkedIn = request('checked_in');

            if ($checkedIn === '1') {
                // Lọc khách đã checkin
                $query = $query->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('checkins')
                        ->whereRaw('clients.event_code = checkins.event_code')
                        ->whereRaw('clients.qrcode = checkins.qrcode')
                        ->where('checkins.status', '!=', Checkin::STATUS_DELETED)
                        ->where('checkins.type', Checkin::TYPE_CHECKIN);
                });
            } elseif ($checkedIn === '0') {
                // Lọc khách chưa checkin
                $query = $query->whereNotExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('checkins')
                        ->whereRaw('clients.event_code = checkins.event_code')
                        ->whereRaw('clients.qrcode = checkins.qrcode')
                        ->where('checkins.status', '!=', Checkin::STATUS_DELETED)
                        ->where('checkins.type', Checkin::TYPE_CHECKIN);
                });
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
                $query->where($key, $value);
            }
        }

        if (isset($dateTimes) && count($dateTimes)) {
            $allowedDateFields = array_keys(Client::getDateFields());

            foreach ($dateTimes as $key => $value) {
                if (!in_array($key, $allowedDateFields, true)) {
                    continue;
                }

                $query->whereDate("clients.{$key}", '>=', $value[0]);
                $query->whereDate("clients.{$key}", '<=', $value[1]);
            }
        }

        return $query;
    }

    public function upload(Event $event)
    {
        $dataFile = $this->attributes['file'];
        $fileService = new ImpexpFileService($this->attributes);
        $fileService->dataFile = $dataFile;
        $fileService->folderName = 'import-clients';
        $fileService->table = 'clients';

        if ($file = $fileService->uploadFile()) {
            Session::put('import_id', $file->id);

            /* Call command importing */
            Artisan::call("import:clients {$file->id}");
            if ($file->error_log) {
                $msg = 'Một số lỗi trong file nạp vào';
                $errorLogs = json_decode($file->error_log, true);
                Session::put('import_clients_errors', $errorLogs);

                if (count($errorLogs) == 1) {
                    $msg = array_values($errorLogs)[0];
                }

                // return [
                //     'success'   => false,
                //     'msg'       => $msg
                // ];
            }

            // Call Job Importing
            $objJob = new GenerateImageQrcode($event->code);
            $objJob->timeout = 600;
            $generateImageQrcodeJob = $objJob->delay(Carbon::now()->addSeconds(1));
            dispatch($generateImageQrcodeJob);

            /* Call Job Importing */
            // $objJob = new ImportExcelClient($file);
            // $objJob->timeout = 600;
            // $importExcelToDb = $objJob->delay(Carbon::now()->addSeconds(1));
            // dispatch($importExcelToDb);

            return [
                'success'   => true,
                // 'msg'       => 'Danh sách đang được xử lý...',
                'msg'       => 'Nạp file thành công',
                'file'      => $file
            ];
        }

        return [
            'success'   => false,
            'msg'       => 'Đã có lỗi xảy ra, chưa hoàn tất nạp file'
        ];
    }

    public function generateClients($event, int $count = 100, ?string $type = null)
    {
        $limitedClients = $event->company->limited_clients;

        if (is_numeric($limitedClients)) {
            $totalCount = $count;

            $clients = $this->getListByAttributes([
                'event_id' => $event->id,
            ]);

            if (!empty($clients) && $clients->count()) {
                $totalCount += $clients->count();
            }

            if ($count > $limitedClients) {
                $count = $limitedClients;
            }

            if ($totalCount > $limitedClients) {
                return [
                    'success'   => false,
                    'msg'       => "Dữ liệu khách hàng ($totalCount) vượt quá giới hạn được phép."
                ];
            }
        }

        try {
            Client::factory()
                ->count($count)
                ->make()
                ->each(function ($client) use ($event, $type) {
                    $client->event_id = $event->id;
                    $client->event_code = $event->code;
                    $client->type = $type;
                    $client->qrcode = $event->generateQrcodeOnSetting($event->code);
                    $client->register_source = Client::REGISTER_ADMIN;
                    $client->updated_by = auth()->user()->id;
                    $client->created_by = auth()->user()->id;
                    $client->save();
                });

            $this->generateQrcodeImages($event->code, null, $type);
            return [
                'success'   => true,
                'msg'       => "Đã tạo mới thành công {$count} khách mời"
            ];
        } catch (\Throwable $th) {
            $msgs = "Lỗi khi tạo mới khách mời";

            if (auth()->user()->isSysAdmin()) {
                $msgs = $th->getMessage();
            }
        }

        return [
            'success'   => false,
            'msg'       => $msgs ?? "Có lỗi xảy ra trong quá trình tạo mới {$count} khách mời"
        ];
    }

    public function generateQrcodeImages($eventCode, ?string $qrcode = null, ?string $type = null)
    {
        $command = "generate:image-qrcode {$eventCode} --qrcode={$qrcode}";
        Artisan::call($command);
        return true;

        /* Call Job Importing */
        $objJob = new GenerateImageQrcode($eventCode, $qrcode, $type);
        $objJob->timeout = 600;
        $generateImageQrcodeJob = $objJob->delay(Carbon::now()->addSeconds(1));
        dispatch($generateImageQrcodeJob);
        return true;
    }

    public function destroy(Client $client, ?string $batchKey = null)
    {
        $fields = $this->client_backup()->getFillable();

        foreach ($fields as $field) {
            $attributes[$field] = $client->$field ?? null;

            /* other attributes */
            $attributes['org_id'] = $client->id;
        }

        $attributes['batch_key'] = $batchKey ?? Str::random(11);
        $this->client_backup()->create($attributes);
        Helper::deleteFileStorage($client->img_qrcode ?? "qrcodes/".strtolower($client->event_code)."/{$client->qrcode}.png");
        Helper::deleteFileStorage($client->document_pdf);
        $client->delete();
        return true;

        /* no need */
        // Only update status to DELETED
        return $this->update($client->id, [
            'name'      => Client::getDeletedStr($client->name),
            'qrcode'    => Client::getDeletedStr($client->qrcode),
            'phone'     => Client::getDeletedStr($client->phone),
            'email'     => Client::getDeletedStr($client->email),
            'status'    => Client::STATUS_DELETED,
        ]);
    }
    // Lấy các custom field có thể dùng để lọc và chuyển về dạng mảng
    public function getFilterCustomFields(int $eventId): array {
        return CustomFieldTemplate::query()
            ->where('event_id', $eventId)
            ->whereIn('type', CustomFieldTemplate::TYPE_USE_OPTIONS)
            ->orderBy('order')
            ->get()
            ->map(fn($tpl) => [
                'key' => $tpl->name,
                'ui'  => $tpl->getTypeGroup($tpl->type),
                'options' => $tpl->getOptionsAsArray(),
                'label'=> $tpl->description ?? $tpl->name,
            ])
            ->values()
            ->all();
    }
    // Áp dụng filter custom field vào query
    public function applyCustomFieldFilters(Builder $query, int $eventId): Builder
    {
        $fields = $this->getFilterCustomFields($eventId);
        // duyệt qua từng field
        foreach ($fields as $field) {
            $param = $field['key'];

            $vals = request()->input($param);
            if (empty($vals)) continue;

            if(!is_array($vals)) {
                $vals = [$vals];
            }
            // chỉ lấy các giá trị hợp lệ
            $allowed = array_keys($field['options'] ?? []);
            $vals = array_values(array_intersect($vals, $allowed));
            if (empty($vals)) continue;
            // áp dụng filter
            $jsonPathKey = "custom_fields->{$param}";
            // nếu là multichoice thì dùng whereJsonContains
            if (($field['ui'] ?? null) === 'multichoice') {
                $query->where(function ($or) use ($jsonPathKey, $vals) {
                    foreach ($vals as $v) {
                        $or->orWhereJsonContains($jsonPathKey, $v);
                    }
                });
            } else {
                // các loại còn lại thì so sánh bằng
                $query->where(function ($or) use ($jsonPathKey, $vals) {
                    foreach ($vals as $v) {
                        $or->orWhere($jsonPathKey, '=', $v);
                    }
                });
            }
        }
        return $query;
    }

    public function sendClientEmail(Client $client, int $campaignId)
    {
        $campaign = Campaign::where('id', $campaignId)->first();
        if (empty($campaign)) return null;
        if (!Helper::checkEmailForm($client->email)) return null;
        $param = [
            'name'          => $client->name,
            'email'         => $client->email,
            'phone'         => $client->phone,
            'qrcode'        => $client->qrcode,
            'img_qrcode'    => route('clients.view-qrcode-by-id', [
                'id'        => $client->id
            ]),
            'document_pdf'  => route('clients.view-document-pdf', [
                'clientId'  => $client->id,
            ]),
            'cc'            => implode(', ', json_decode($campaign->cc, true)),
            'bcc'           => implode(', ', json_decode($campaign->bcc, true)),
            'send_email'    => 1,
        ];
        $attr = [
            'id'            => null,
            'campaign_id'   => $campaign->id,
            'subject'       => $campaign->subject,
            'from_name'     => $campaign->from_name,
            'from_email'    => $campaign->from_email,
            'template_id'   => $campaign->template_id,
            'is_online'     => 1, // $campaign->is_online
            'param'         => json_encode(array_merge($param, $client->getCustomFieldValues(false) ?? [])),
            'email'         => $client->email,
            'qrcode'        => $client->qrcode,
            'to_name'       => $client->name,
            'to_email'      => $client->email,
            'status'        => Email::STATUS_WAITING,
        ];

        $email = $this->email()->create($attr);
        return $email ?? null;
    }
}
