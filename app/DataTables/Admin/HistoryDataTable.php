<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Helpers\Helper;
use App\Models\Client;
use App\Models\Company;
use App\Models\History;
use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class HistoryDataTable extends BaseDataTable
{
    public function __construct()
    {

    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $query = $this->getFilter($query);

        return datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->addColumn('user', function(History $model) {
                return $model->user_id ? '<span class="text-sm">'."{$model->user->name} - {$model->user->email}".'</span>' : null;
            })
            ->addColumn('detail', function(History $model) {
                $text = ($model->getFunctionText() ?? $model->function);
                $text = '<span class="fst-italic text-sm">'.$text.'</span>';

                if ($model->parameters) {
                    if (auth()->user()->isSysAdmin()) {
                        $btns = '<a href="" data-bs-toggle="modal" data-bs-target="#parameters-'.$model->id.'Modal">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>';
                        $btns .= view('admin.historys._modal-info', [
                            'data'      => $model->parameters ?? [],
                            'modalId'   => "parameters-{$model->id}Modal",
                        ]);
                    }
                }

                if ($model->error) {
                    $btns .= '<a href="" class="text-danger" data-bs-toggle="modal" data-bs-target="#error-'.$model->id.'Modal">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </a>';
                    $btns .= view('admin.historys._modal-info', [
                        'data'      => $model->error ?? [],
                        'modalId'   => "error-{$model->id}Modal",
                    ]);
                }
                return "{$text} ".($btns ?? null);
            })
            ->editColumn('created_at', function(History $model) {
                return $model->created_at ? humanize_date($model->created_at, 'd/m/Y H:i') : null;
            })
            ->setRowClass(function (History $model) {
                if ($model->error) {
                    return "table-danger";
                }

                return '';
            });
    }

    public function getFilter($query = null)
    {
        $query = $this->query(new History()); // Get the base query

        if (!auth()->user()->isSysAdmin()) {
            $usersArray = auth()->user()
                ->company
                ->users
                ->pluck('id')
                ->toArray();

            $query->where('user_id', $usersArray);
        }

        $query->orderBy('created_at', 'DESC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\History $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(History $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('history-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtlip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    )
                    ->parameters([
                        'pageLength'    => 50,
                        'lengthMenu'    => [5, 10, 20, 30, 50, 100, 200, 500],
                        'processing'    => true,
                        'stateSave'     => true,
                        'language'      => $this->getL(),
                        'buttons'       => [
                            [
                                'text'          => '<i class="bx bxs-printer"></i>',
                                'className'     => 'btn buttons-print btn-default',
                            ],
                        ],

                    ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('user')
                ->title("User"),
            Column::make('detail')
                ->title("Mô tả"),
            // Column::make('parameters')
            //     ->title("Chi tiết"),
            // Column::make('error')
            //     ->title("Lỗi"),
            Column::make('created_at')
                ->title("Thời gian"),
        ];
    }
}
