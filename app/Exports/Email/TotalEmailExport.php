<?php

namespace App\Exports\Email;

use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Email;
use App\Models\Campaign;
use App\Models\Event;

class TotalEmailExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    use Exportable;
    public $eventCode;
    public $campaignId;
    private $rows;

    public function __construct(string $eventCode, int $campaignId = 0)
    {
        $this->eventCode = $eventCode;
        $this->campaignId = $campaignId;
    }

    public function query()
    {
        if ($this->campaignId) {
            return Email::query()->where('campaign_id', $this->campaignId);
        }

        if (!empty($this->eventCode)) {
            $campaignIds = [];

            $event = Event::where([
                'code' => $this->eventCode
            ])->first();

            if (empty($event)) {
                return false;
            }

            $campaigns = Campaign::where([
                'event_id' => $event->id
            ])->get();

            foreach ($campaigns as $campaign) {
                array_push($campaignIds, $campaign->id);
            }

            return Email::query()->whereIn('campaign_id', $campaignIds);
        }
    }

    public function map($email): array
    {
        ++$this->rows;

        $values = [
            $this->rows,
            $email->campaign_id,
            $email->template_id,
            $email->param,
            $email->to_email,
            $email->to_name,
            $email->subject,
            $email->from_email,
            $email->from_name,
            $email->created_at,
            $email->sent_at,
        ];

        return $values;
    }

    public function headings(): array
    {
        $headings = [
            '#',
            'CAMPAIGN ID',
            'TEMPLATE ID',
            'EMAIL PARAM',
            'EMAIL',
            'NAME',
            'SUBJECT',
            'FROM EMAIL',
            'FROM NAME',
            'STARTED',
            'PROCESSED'
        ];

        return $headings;
    }

    public function title(): string
    {
        return "Tổng hợp";
    }
}
