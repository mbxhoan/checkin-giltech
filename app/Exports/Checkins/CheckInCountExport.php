<?php

namespace App\Exports\Checkins;

use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Services\Admin\CheckinService;
use App\Models\Checkin;
use App\Models\Event;

class CheckInCountExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
{
    use Exportable;
    private $event;
    private $qrcode;
    private $rows = 0;
    private $service;
    private $limitedClients;
    private $hiddenColumns = [];
    private $customFieldTemplates = [];

    public function __construct(Event $event, ?string $qrcode = null)
    {
        $this->event = $event;
        $this->qrcode = $qrcode;
        $this->service = new CheckinService();
        $this->limitedClients = $event->company->limited_clients;
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();
    }

    public function query()
    {
        $query = Checkin::query()
            ->join('clients', function ($join) {
                $join->on('checkins.qrcode', '=', 'clients.qrcode')
                    ->on('checkins.event_id', '=', 'clients.event_id');
            })
            ->where('checkins.event_id', $this->event->id)
            ->where('checkins.status', '!=', Checkin::STATUS_DELETED);

        $query = $query->selectRaw('
                checkins.event_id,
                checkins.event_code,
                checkins.qrcode,
                count(*) as total_client_checkin
            ')
            ->orderBy('checkins.qrcode', 'ASC')
            ->groupBy([
                'checkins.event_id',
                'checkins.event_code',
                'checkins.qrcode',
            ]);

        if ($this->qrcode) {
            $query->where('checkins.qrcode', $this->qrcode);
        }

        return $query;
    }

    public function map($checkin): array
    {
        /* set limited */
        if (is_numeric($this->limitedClients) && $this->rows > $this->limitedClients) {
            return [
                'hidden'
            ];
        }

        $values = [];
        $client = $checkin->client;

        $defaultValues = [
            ++$this->rows,
            $checkin->qrcode,
            $client->name ?? null,
            $client->email ?? null,
            $checkin->total_client_checkin,
            $client->register_source ?? null,
            $client->type ?? null,
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
            'ID',
            'QRCODE',
            'NAME',
            'EMAIL',
            'COUNT',
            'REGISTER_SOURCE (Nguồn đăng ký)',
            'TYPE (Loại/Nhóm)',
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
        return "Báo cáo số lần checkin";
    }
}
