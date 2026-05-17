<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CompanyDataTable extends BaseDataTable
{
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
            // ->addColumn('action', function(Company $model){
            //     return '<input type="checkbox" id="check-'.$model->id.'" class="item-check" data-id="'.$model->id.'" />';
            // })
            ->editColumn('code', function(Company $model){
                $route = route('admin.companys.edit', $model->id);
                return '<a href="'.$route.'"><b>'.$model->code.'</b> <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('name', function(Company $model){
                $route = route('admin.companys.edit', $model->id);
                return '<a href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->addColumn('events_count', function(Company $model) {
                $route = route('admin.events.index', [
                    'company_id' => $model->id
                ]);

                $btns = '<a href="'.$route.'"><i class="fa-solid fa-calendar-days"></i> <b>'.$model->events_count.'</b></a>';
                $routeCreateEvent = route('admin.events.create', [
                    'company_id' => $model->id
                ]);

                $btns .= '<a target="_blank" href="'.$routeCreateEvent.'"><i class="fa-regular fa-plus-square fa-fw"></i></a>';
                return $btns;
            })
            ->addColumn('users_count', function(Company $model) {
                $route = route('admin.users.index', [
                    'company_id' => $model->id
                ]);

                $btns = '<a href="'.$route.'"><i class="fa-solid fa-users"></i> <b>'.$model->users_count.'</b></a>';
                $routeCreateUser = route('admin.users.create', [
                    'company_id' => $model->id
                ]);

                $btns .= '<a target="_blank" href="'.$routeCreateUser.'"><i class="fa-regular fa-plus-square fa-fw"></i></a>';
                return $btns;
            })
            ->editColumn('updated_by', function(Company $model){
                return $model->update_by ? $model->user->name : null;
            })
            ->editColumn('status', function(Company $model){
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Company()); // Get the base query
        }

        $query->where('status', '!=', Company::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        $query->orderBy('status', 'ASC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Company $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Company $model)
    {
        return $model->newQuery()
            ->withCount([
                'events as events_count' => function ($query) {
                    $query->where('status', '!=', Event::STATUS_DELETED);
                },
                'users as users_count' => function ($query) {
                    $query->where('status', '!=', User::STATUS_DELETED);
                },
            ]);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('company-table')
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
            Column::make('name')
                ->title('Tên công ty'),
            Column::make('code')
                ->title('Mã công ty'),
            Column::make('events_count')
                ->title("Sự kiện"),
            Column::make('users_count')
                ->title("User(s)"),
            Column::make('status')
                ->title('Trạng thái')
                ->addClass('text-center'),
            Column::make('limited_clients')
                ->title('Giới hạn data'),
            Column::make('limited_events')
                ->title('Giới hạn sự kiện'),
            Column::make('updated_at')
                ->title('Ngày cập nhật'),
        ];
    }
}
