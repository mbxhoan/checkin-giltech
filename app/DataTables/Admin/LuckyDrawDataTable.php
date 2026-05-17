<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\LuckyDraw;
use App\Models\Event;
use App\Services\Admin\LuckyDrawService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LuckyDrawDataTable extends BaseDataTable
{
    public $service;
    private $event = null;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
        $this->service = app(LuckyDrawService::class);
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
            ->editColumn('status', function(LuckyDraw $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('event_id', function(LuckyDraw $model) {
                $route = route('admin.events.edit', $model->event);
                $text = '<span class="fw-bold text-sm" id="code-'.$model->event_id.'"><b>'.$model->event->code.'</b></span>';
                $btnCopy = '<a class="" data-clipboard-target="#code-'.$model->event_id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return '<a href="'.$route.'"><b>'.$text.'</b></a> '.$btnCopy;
            })
            ->addColumn('size', function(LuckyDraw $model) {
                return "{$model->width} x {$model->height} {$model->unit}";
            })
            ->editColumn('type', function(LuckyDraw $model) {
                return $model->type ?? "Tất cả";
            })
            ->editColumn('name', function(LuckyDraw $model) {
                $text = $model->name;

                if ($model->is_default) {
                    $text = '<b>'.$model->name.' <span class="text-xs text-warning">*</span></b>';
                }

                return '<a href="'.route('admin.lucky_draws.edit', $model).'" target="">'.$text.' <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('updated_at', function(LuckyDraw $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('actions', function(LuckyDraw $model) {
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (LuckyDraw $model) {
                if ($model->is_default) {
                    return "table-light";
                }

                return null;
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new LuckyDraw()); // Get the base query
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

        $query->where('status', '!=', LuckyDraw::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\LuckyDraw $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LuckyDraw $model)
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
                    ->setTableId('lucky_draws-table')
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
                ->title("Tên"),
            Column::make('event_id')
                ->title("Sự kiện"),
            Column::make('type')
                ->title("Loại"),
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
            'route' => route('admin.lucky_draws.edit', $model),
            'class' => 'btn btn-sm btn-primary mb-1',
        ]);

        $buttons .= view('components.btn-del-alert', [
            'route'     => route('admin.lucky_draws.destroy', $model),
            'class'     => 'btn btn-sm btn-danger mb-1',
            'confirm'   => "Bạn có chắc chắn muốn xóa quay số \"{$model->name}\"?",
            'modalId'   => "deleteLuckyDraw{$model->id}",
        ]);

        return $buttons;
    }
}
