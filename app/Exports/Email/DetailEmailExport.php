<?php

namespace App\Exports\Email;

use App\Models\Email;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailEmailExport implements FromView, WithTitle
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

    public function view(): View
    {
        $eventCode = $this->eventCode;

        if ($this->campaignId) {
            $emails = Email::where('campaign_id', $this->campaignId)->get();
        } else {
            $emails = Email::whereIn('campaign_id', function ($query) use ($eventCode) {
                $query->select('campaigns.id')
                    ->from('campaigns')
                    ->join('events', 'campaigns.event_id', '=', 'events.id')
                    ->where('events.code', $eventCode);
            })->get();
        }

        return view('admin.emails.tables._excel', [
            'emails' => $emails
        ]);
    }

    public function title(): string
    {
        return "Chi tiết";
    }
}
