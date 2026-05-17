<?php

namespace App\Exports\Clients;

use App\Models\Checkin;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Services\Admin\ClientService;
use App\Models\Client;
use App\Models\Event;

class ClientExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
{
    use Exportable;
    private $limitedClients;
    private $event;
    private $rows = 0;
    private $service;
    private $hiddenColumns = [];
    private $customFieldTemplates = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->service = new ClientService();
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();
        $this->limitedClients = $event->company->limited_clients;
    }

    public function query()
    {
        $query = Client::query()
            ->where('event_id', $this->event->id)
            ->where('status', '!=', Client::STATUS_DELETED);

        $query = $this->service->applyFilters($query);
        $query = $this->service->applyCustomFieldFilters($query, $this->event->id);

        return $query;
    }

    public function map($client): array
    {
        ++$this->rows;
        $checkin = Checkin::where([
            'event_code'    => $this->event->code,
            'qrcode'        => $client->qrcode
        ])->first();

        /* set limited */
        if (is_numeric($this->limitedClients) && $this->rows > $this->limitedClients) {
            return [
                'hidden'
            ];
        }

        $values = [];

        $defaultValues = [
            !empty($checkin) ? "CHECKED" : "",
            $client->ref_id,
            $client->id,
            $client->getStatusText(),
            $client->name,
            $client->qrcode,
            !empty($client->qrcode) ? route('clients.view-qrcode-by-id', [
                'id' => $client->id
            ]) : '',
            !empty($client->document_pdf) ? route('clients.view-document-pdf', [
                'clientId'  => $client->id,
            ]) : '',
            $client->email,
            $client->register_source,
            $client->type,
            $client->created_at,
            $client->updated_at,
            $client->updated_by ? $client->user->name : ''
        ];

        $customValues = [];

        if (!empty($client->custom_fields) && !empty($this->customFieldTemplates)) {
            foreach ($this->customFieldTemplates as $fieldName => $fieldAttr) {
                if (!empty($this->hiddenColumns) && in_array($fieldName, $this->hiddenColumns)) {
                    continue;
                }

                $customValues[] = $client->getCustomFieldValue($fieldAttr, false);
            }
        }

        if (count($customValues) > 0) {
            $values = array_merge($defaultValues, $customValues);
        } else {
            $values = $defaultValues;
        }

        return $values;
    }

    public function headings(): array
    {
        $customHeadings = [];

        $headings = [
            'CHECKIN',
            'REF_ID',
            'ID',
            'STATUS',
            'NAME',
            'QRCODE',
            'QR_LINK',
            'IMAGE_LINK',
            'EMAIL',
            'REGISTER_SOURCE (Nguồn đăng ký)',
            'TYPE (Loại/Nhóm)',
            'CREATED_AT (Thời gian tạo mới)',
            'UPDATED_AT (Thời gian cập nhật)',
            'UPDATED_BY (Người cập nhật',
        ];

        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();

        if (count($this->customFieldTemplates)) {
            foreach ($this->customFieldTemplates as $fieldName => $customFieldTemplate) {
                /* check có trong hidden columns không */
                if (!empty($this->hiddenColumns) && count($this->hiddenColumns)) {
                    if (in_array($fieldName, $this->hiddenColumns)) {
                        continue;
                    }
                }

                $customHeadings[] = mb_strtoupper($fieldName);
            }
        }

        return array_merge($headings, $customHeadings);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return "Danh sách khách mời";
    }
}
