<?php

namespace App\Console\Commands\Email;

use App\Exports\Email\EmailExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportEmailCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:email-report {eventCode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $eventCode = $this->argument('eventCode');
        $exportPath = "exports/excels/emails/{$eventCode}";
        return Excel::store(new EmailExport($eventCode), "{$exportPath}/EmailReport_{$eventCode}_".date('Ymd_His').'.xlsx');
    }
}
