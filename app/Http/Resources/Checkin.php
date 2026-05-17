<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Checkin extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'qrcode'        => $this->qrcode,
            'name'          => $this->client_name,
            'checkin_count' => $this->checkin_count,
            'checkin_at'    => $this->scan_time,
        ];
    }
}
