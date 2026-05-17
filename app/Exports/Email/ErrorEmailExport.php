<?php

namespace App\Exports\Email;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\CampaignDetail;
use App\Models\Campaign;

class ErrorEmailExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    private $rows;
    private $campaign_id;

    public function __construct(int $campaign_id)
    {
        $this->campaign_id = $campaign_id;
    }

    public function query()
    {
        if (!empty($this->campaign_id)) {
            $campaign = Campaign::where([
                'id' => $this->campaign_id
            ])->first();

            if (empty($campaign)) return;

            return CampaignDetail::query()->where([
                'campaign_id'   => $campaign->id,
                'email_form'    => false,
            ]);
        }

        return;
    }

    public function map($email): array
    {
        ++$this->rows;

        $values = [
            $this->rows,
            $email->campaign_id,
            $email->email,
            $email->name,
            $email->created_at,
        ];

        return $values;
    }

    public function headings(): array
    {
        $headings = [
            '#',
            'CAMPAIGN ID',
            'EMAIL',
            'NAME',
            'CREATED AT'
        ];

        return $headings;
    }
}
