<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Language extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'is_default'    => $this->is_default,
            'name'          => $this->name,
            'code'          => $this->code,
		    'description'   => $this->description,
            'icon'          => asset("storage/{$this->icon_path}"),
		    'status'        => $this->status
        ];
    }
}
