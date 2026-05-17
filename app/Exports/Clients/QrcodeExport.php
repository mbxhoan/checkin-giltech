<?php

namespace App\Exports\Clients;

use App\Models\Client;
use App\Models\Event;
use App\Services\Admin\ClientService;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Contracts\Queue\ShouldQueue;

class QrcodeExport implements FromQuery, WithDrawings, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $clients;
    private $limitedClients;
    private $event;
    private $service;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->service = new ClientService();
        $this->limitedClients = $event->company->limited_clients;
    }

    public function query()
    {
        $query = Client::query()
            ->where('event_id', $this->event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->orderBy('updated_at', 'ASC');

        $query = $this->service->applyFilters($query);

        /* set limited */
        if (is_numeric($this->limitedClients)) {
            $query = $query->limit($this->limitedClients);
        }

        $this->clients = $query->get();
        return $query;
    }

    public function headings(): array
    {
        return [
            'QRCODE',
            'NAME',
            'LINK',
            'QRCODE_OUTPUT'
        ];
    }

    public function map($model): array
    {
        if (!$model->img_qrcode) return [];

        return [
            $model->qrcode, // or $model->qrcode_output if that's the real attribute
            $model->name,
            route('clients.view-qrcode-by-id', [
                'id' => $model->id
            ]),
            '' // Placeholder for column D where QR code image will be inserted
        ];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->clients as $index => $client) {
            if (!$client->img_qrcode) continue;
            $drawing = new Drawing();
            $drawing->setName('QR Code');
            $drawing->setDescription('QR Code');
            $drawing->setOffsetX(20); // Fine-tune horizontal position
            $drawing->setOffsetY(20); // Fine-tune vertical position
            // $drawing->setPath(public_path("storage/{$client->img_qrcode}"));
            $drawing->setPath(storage_path("app/public/{$client->img_qrcode}"));
            $drawing->setHeight(100);
            $row = $index + 2; // Row 1 = header
            $drawing->setCoordinates('D'.$row);
            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range(2, count($this->clients) + 1) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(100);
        }

        // Optional: force auto-size for A & B (redundant if using ShouldAutoSize)
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->getColumnDimension('D')->setWidth(20);

        // Center align all QR code cells
        $sheet->getStyle('D2:D'.(count($this->clients) + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D'.(count($this->clients) + 1))
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
}
