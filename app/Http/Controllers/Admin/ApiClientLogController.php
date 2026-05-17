<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\ApiClientLogDataTable;
use App\Http\Controllers\Controller;

class ApiClientLogController extends Controller
{
    public function index(ApiClientLogDataTable $dataTable)
    {
        abort_unless(auth()->check() && auth()->user()->isSysAdmin(), 404);

        return $dataTable->render('admin.api_client_logs.index');
    }
}
