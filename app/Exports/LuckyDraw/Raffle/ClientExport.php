<?php

namespace App\Exports\LuckyDraw\Raffle;

use App\Models\LuckyDrawClient;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
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
        return LuckyDrawClient::query()
            ->with('reward')
            ->where('lucky_draw_id', $this->luckyDrawId);
    }

    public function map($row): array
    {
        ++$this->rows;

        return [
            $this->rows,
            $row->qrcode,
            $row->name,
            $row->email,
            $row->phone,
            $row->reward ? $row->reward->code : null,
            $row->reward ? $row->reward->name : null,
            $row->reward ? $row->reward->order_name : null,
            $row->updated_at,
            $row->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'STT',
            'QRCODE',
            'TÊN',
            'EMAIL',
            'SDT',
            'MÃ GIẢI',
            'TÊN GIẢI',
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
