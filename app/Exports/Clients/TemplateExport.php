<?php

namespace App\Exports\Clients;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\FromArray;
use App\Models\Event;

class TemplateExport implements FromArray, WithHeadings, WithColumnFormatting
{
    use Exportable;
    protected $event;
    protected $count = 0;
    protected $type = null;
    private $rows = 0;
    private $customFieldTemplates = [];
    private $hiddenColumns = [];

    public function __construct(Event $event, ?int $count = 0, ?string $type = null)
    {
        $this->event = $event;
        $this->count = $count+1;
        $this->type = $type;
    }

    public function array(): array
    {
        $data = [];

        if ($this->count > 0) {
            for ($i = 1; $i < $this->count; $i++) {
                $qrcode = $this->event->generateQrcodeOnSetting(
                    $this->event->code,
                );

                $data[] = [
                    $qrcode,
                    null,
                    null,
                    $this->type
                ];
            }

            return $data;
        }

        return $data; // No data rows
    }

    public function headings(): array
    {
        $headings = [];
        $customHeadings = [];

        $headings = [
            'QRCODE',
            'NAME',
            'EMAIL',
            'TYPE',
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
}
