<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
                'align_in_bootstrap'        => $this->getAlignInBootstrap(),
                'logo'                      => !empty($this->logoUrl) ? $this->logoUrl->getUrl() : null,
                'favicon'                   => !empty($this->faviconUrl) ? $this->faviconUrl->getUrl() : null,
                'main_bg_desktop'           => !empty($this->mainBgDesktop) ? $this->mainBgDesktop->getUrl() : null,
                'main_bg_mobile'            => !empty($this->mainBgMobile) ? $this->mainBgMobile->getUrl() : null,
                'custom_field_templates'    => $this->getCustomFieldTemplates(true, true),
            ]
        );
    }
}
