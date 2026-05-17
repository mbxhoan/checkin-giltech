<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class Client extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(
        Arr::except(parent::toArray($request), [
                'ref_id',
                'lp_id',
                'lang',
                'card_link_mobile',
                'card_link_desktop',
                'avatar',
                'created_by',
                'updated_by',
                'event',
                // add more fields you don’t want
            ]),
            [
                'img_qrcode' => $this->img_qrcode ? route('clients.view-qrcode-by-id', [
                    'id' => $this->id,
                ]) : null,
                'document_pdf' => $this->document_pdf ? route('clients.view-document-pdf', [
                    'clientId' => $this->id,
                ]) : null,
            ]
        );
    }
}
