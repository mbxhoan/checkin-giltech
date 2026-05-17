<?php
namespace App\Services\Api;

use App\Models\Checkin;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;

//Log::info("Data of PDA send to server is not correct, ticket={$ticket->id} | pda={$userId} | item_code={$item['item']} | item={$item['name']}");

class CheckinService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Checkin::class);
    }

    // public function middleware_checkin()
    // {
    //     return app(MiddlewareCheckinService::class);
    // }

    public function checkin()
    {
        $checkin = new MiddlewareCheckinService(
            $this->attributes['event_code'],
            $this->attributes['qrcode'],
            $this->attributes['scan_time'],
        );

        $checkin->attributes = [
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'],
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
