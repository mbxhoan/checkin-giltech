<?php

namespace App\Exports\LuckyDraw\Raffle;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResultExport implements WithMultipleSheets
{
    use Exportable;
    public $luckyDrawId;

    public function __construct(int $luckyDrawId)
    {
        $this->luckyDrawId = $luckyDrawId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ClientExport($this->luckyDrawId, "Danh sách người tham dự");
        $sheets[] = new RewardExport($this->luckyDrawId, "Danh sách giải");
        return $sheets;
    }
}
