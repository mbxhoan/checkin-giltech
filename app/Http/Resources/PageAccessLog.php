<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageAccessLog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'updated_at'    => humanize_date($this->updated_at, "Y-m-d H:i:s"),
                'created_at'    => humanize_date($this->created_at, "Y-m-d H:i:s"),
            ]
        );
    }
}
