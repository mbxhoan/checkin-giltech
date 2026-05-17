<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Label;
use App\Models\Event;
use App\Services\Admin\LabelService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LabelDataTable extends BaseDataTable
{
    public $service;
    private $event = null;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
        $this->service = app(LabelService::class);
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
            ->editColumn('status', function(Label $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('event_id', function(Label $model) {
                $route = route('admin.events.edit', $model->event);
                $text = '<span class="fw-bold text-sm" id="code-'.$model->event_id.'"><b>'.$model->event->code.'</b></span>';
                $btnCopy = '<a class="" data-clipboard-target="#code-'.$model->event_id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return '<a href="'.$route.'"><b>'.$text.'</b></a> '.$btnCopy;
            })
            ->addColumn('size', function(Label $model) {
                return "{$model->width} x {$model->height} {$model->unit}";
            })
            ->editColumn('type', function(Label $model) {
                return $model->type ?? "Tất cả";
            })
            ->editColumn('name', function(Label $model) {
                $text = $model->name;

                if ($model->is_default) {
                    $text = '<b>'.$model->name.' <span class="text-xs text-warning">*</span></b>';
                }

                return '<a href="'.route('admin.labels.edit', $model).'" target="">'.$text.' <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('updated_at', function(Label $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('actions', function(Label $model) {
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Label $model) {
                if ($model->is_default) {
                    return "table-light";
                }

                return null;
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Label()); // Get the base query
        }

        if (!auth()->user()->isSysAdmin()) {
            $companyId = auth()->user()->company->id;

            $query->whereHas('event', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        if (!empty($this->event)) {
            $query->where('event_id', $this->event->id);
        }

        $query->where('status', '!=', Label::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Label $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Label $model)
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
                    ->setTableId('labels-table')
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
                ->title("Thông tin"),
            Column::make('size')
                ->title("Kích thước"),
            Column::make('event_id')
                ->title("Sự kiện"),
            Column::make('type')
                ->title("Nhóm khách"),
            Column::make('status')
                ->title("Trạng thái"),
            Column::make('updated_at')
                ->title("Cập nhật lúc"),
            Column::make('actions')
                ->title(""),
        ];
    }

    protected function generateActionBtns($model)
    {
        $buttons = view('components.btn-edit', [
            'route' => route('admin.labels.edit', $model),
            'class' => 'btn btn-sm btn-primary',
        ]);

        return $buttons;
    }
}
