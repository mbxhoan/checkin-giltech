<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Admin\HistoryDataTable;

class HistoryController extends Controller
{
    public function __construct()
    {

    }

    /**
     * Show the application clients index.
     */
    public function index()
    {
        $dataTable = new HistoryDataTable();
        return $dataTable->render('admin.historys.index');
    }
}
