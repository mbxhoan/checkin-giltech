<?php
namespace App\Services\Scan;

use App\Models\EventSetting;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Services\Middleware\EmailService as MiddlewareEmailService;

class ScanService extends BaseService
{
    public function __construct() {}

    public function client()
    {
        return app(ClientService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
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
            $this->attributes['by_pass_duplicate'],
        );

        $checkin->attributes = [
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP,
            'type'          => $this->attributes['type'] ?? null,
        ];

        return $checkin->checkin();
    }

    public function multiCheckin()
    {
        $checkin = new MiddlewareCheckinService(
            $this->attributes['event_code'],
        );

        $checkin->attributes = [
            'data'          => $this->attributes['data'],
            'total_records' => $this->attributes['total_records'],
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'],
        ];

        return $checkin->multiCheckin();
    }
}
