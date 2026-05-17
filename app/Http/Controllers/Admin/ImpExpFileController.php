<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ImpexpFileService;
use App\Models\ImpExpFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ImpExpFileController extends Controller
{
    public function __construct(ImpexpFileService $service)
    {
        $this->service = $service;
    }

    public function getProgress(ImpExpFile $imp_exp_file)
    {
        $this->authorize('view_progress', $imp_exp_file);

        $completed = Cache::get("import_progress_{$imp_exp_file->id}") ?? [];
        $completed = $completed['processed'] ?? 0;
        $errorLogs = json_decode($imp_exp_file->error_log, true);
        Session::put("import_clients_errors_{$imp_exp_file->event_id}", $errorLogs);

        return $this->responseSuccess([
            'html'          => view('components._progress', [
                'total'     => $imp_exp_file->total_record_before,
                'completed' => $completed,
                'dataTime'  => 5,
                'dataEle'   => '#progress',
                'dataUrl'   => route('admin.imp_exp_files.progress', $imp_exp_file),
            ])->render(),
            'reload'        => ($imp_exp_file->status == $imp_exp_file::STATUS_IMPORTED || $imp_exp_file->total_record_before == $completed) ? true : false,
        ]);
    }
}
