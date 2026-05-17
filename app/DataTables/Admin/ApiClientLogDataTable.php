<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\ApiClientLog;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ApiClientLogDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->editColumn('source', function (ApiClientLog $model) {
                return '<span class="badge bg-dark">'.$model->source_label.'</span>';
            })
            ->editColumn('method', function (ApiClientLog $model) {
                return '<span class="badge '.$model->method_badge_class.'">'.$model->method.'</span>';
            })
            ->editColumn('status', function (ApiClientLog $model) {
                return '<span class="badge '.$model->status_badge_class.'">'.$model->status.'</span>';
            })
            ->editColumn('endpoint', function (ApiClientLog $model) {
                return '<span class="text-break">'.e(Str::limit($model->endpoint, 120)).'</span>';
            })
            ->addColumn('detail', function (ApiClientLog $model) {
                $requestModalId = "api-client-log-request-{$model->id}";
                $responseModalId = "api-client-log-response-{$model->id}";

                return view('admin.api_client_logs._detail', [
                    'model' => $model,
                    'requestModalId' => $requestModalId,
                    'responseModalId' => $responseModalId,
                ])->render();
            })
            ->editColumn('created_at', function (ApiClientLog $model) {
                return $model->created_at ? humanize_date($model->created_at, 'd/m/Y H:i:s') : null;
            })
            ->setRowClass(function (ApiClientLog $model) {
                return match (strtoupper((string) $model->status)) {
                    'ERROR' => 'table-warning',
                    'EXCEPTION' => 'table-danger',
                    default => '',
                };
            });
    }

    public function query(ApiClientLog $model)
    {
        return $model->newQuery()->orderByDesc('created_at');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('api-client-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtlip')
            ->orderBy(5, 'desc')
            ->buttons(
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            )
            ->parameters([
                'pageLength' => 50,
                'lengthMenu' => [10, 25, 50, 100, 200],
                'processing' => true,
                'stateSave' => true,
                'language' => $this->getL(),
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('source')->title('Nguồn')->searchable(false)->orderable(false),
            Column::make('method')->title('Method'),
            Column::make('endpoint')->title('Endpoint'),
            Column::make('status')->title('Status'),
            Column::make('detail')->title('Chi tiết')->searchable(false)->orderable(false),
            Column::make('created_at')->title('Thời gian'),
        ];
    }
}
