<?php

namespace App\Console\Commands;

use App\Imports\ClientImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ImpExpFile;
use App\Services\Admin\ImpexpFileService;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImportExcelCmd extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clients {impExpId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data clients from excel file';

    protected $importInfo = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImpexpFileService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->import();
        return Command::SUCCESS;
    }

    /**
     * Teset Import excel to db
     */
    public function import()
    {
        $totalRow = 0;
        $impExpId = (int)$this->argument('impExpId');

        $modelImp = $this->service->findByAttributes([
            'id'        => $impExpId,
            'status'    => ImpExpFile::STATUS_NEW,
        ]);

        if (empty($modelImp)) {
            $this->error("KHÔNG TÌM THẤY ID SẴN SÀNG");
            return false;
        }

        $this->line("IMPORTING...");

        try {
            $excelFile = Storage::path($modelImp->file_path);

            $this->service->update($modelImp->id, [
                'error_log'     => null,
            ]);

            switch ($modelImp->table) {
                case 'clients':
                    $event = $this->service->event()->findByAttributes([
                        'id' => $modelImp->event_id,
                    ]);

                    if (!$event) {
                        $this->error("Không tìm thấy sự kiện");
                        break;
                    };

                    $modelImport = new ClientImport($event);
                    $modelImport->modelImp = $modelImp;
                    Excel::import($modelImport, $excelFile);
                    $totalRow = $modelImport->getRowCount();
                    $this->info("Đã xử lý {$totalRow} dòng");

                    if (!empty($modelImport->getErrors())) {
                        $errorLogs = $modelImport->getErrors();

                        foreach ($errorLogs as $row => $errorLog) {
                            foreach ($errorLog as $error) {
                                $this->error("Dòng {$row}: {$error}");
                            }
                        }
                    }

                    $this->service->update($modelImp->id, [
                        'error_log'     => !empty($errorLogs) ? json_encode($errorLogs) : null,
                        'total_record'  => $totalRow,
                        'status'        => ImpExpFile::STATUS_IMPORTED
                    ]);
                    break;

                default:

                    break;
            }

            $this->info('Imported');
        } catch (Exception $e) {
            $this->service->update($modelImp->id, [
                'error_log'             => json_encode([
                    'error'             => $e->getMessage()
                ]),
                'total_record'          => 0,
                'status'                => ImpExpFile::STATUS_IMPORTED
            ]);

            $this->error($e->getMessage());
        }
    }
}
