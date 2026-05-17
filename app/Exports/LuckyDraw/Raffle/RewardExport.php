<?php

namespace App\Exports\LuckyDraw\Raffle;

use App\Models\LuckyDrawReward;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;

class RewardExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
{
    use Exportable;
    private $rows;
    private $luckyDrawId;
    private $sheetTitle;

    public function __construct(int $luckyDrawId, string $sheetTitle)
    {
        $this->luckyDrawId = $luckyDrawId;
        $this->sheetTitle = $sheetTitle;
    }

    public function query()
    {
        $query = LuckyDrawReward::query()->where([
            'lucky_draw_id' => $this->luckyDrawId
        ]);

        return $query;
    }

    public function map($row): array
    {
        ++$this->rows;

        return [
            $row->is_given ? "ĐÃ TRÚNG" : "",
            $row->code,
            $row->name,
            $row->value,
            $row->order,
            $row->order_name,
            $row->updated_at,
            $row->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'TRẠNG THÁI',
            'MÃ',
            'TÊN',
            'GIÁ TRỊ',
            'THỨ TỰ',
            'LOẠI GIẢI',
            'THỜI GIAN CẬP NHẬT',
            'THỜI GIAN TẠO',
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'A' => NumberFormat::FORMAT_TEXT,
            // 'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
