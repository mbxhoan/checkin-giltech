<?php

namespace App\Exports\Email;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmailExport implements WithMultipleSheets
{
    use Exportable;

    public $eventCode;
    public $campaignId;

    public function __construct(string $eventCode, int $campaignId = 0)
    {
        $this->eventCode = $eventCode;
        $this->campaignId = $campaignId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new TotalEmailExport($this->eventCode, $this->campaignId);
        $sheets[] = new DetailEmailExport($this->eventCode, $this->campaignId);
        return $sheets;
    }
}
