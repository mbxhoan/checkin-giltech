<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\LuckyDrawClient;
use App\Models\LuckyDraw;
use App\Services\Admin\LuckyDrawClientService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LuckyDrawClientDataTable extends BaseDataTable
{
    public $service;
    private $luckyDraw;

    public function __construct(LuckyDraw $luckyDraw)
    {
        $this->luckyDraw = $luckyDraw;
        $this->service = app(LuckyDrawClientService::class);
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
        $limitedClients = $this->luckyDraw->event->company->limited_clients;

        $dataTable = datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->addIndexColumn()
            ->editColumn('status', function(LuckyDrawClient $model) {
                return '<label class="btn btn-xs '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('qrcode', function(LuckyDrawClient $model) use ($limitedClients) {
                $btn = '<a class="" data-clipboard-target="#qrcode-'.$model->id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;

                $btn .= ' <span id="qrcode-'.$model->id.'" class="fw-bold">'.$model->qrcode.'</span>';
                return $btn;
            })
            ->editColumn('name', function(LuckyDrawClient $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;
                return $model->name;
                return '<a href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit"></i></a>';
            })
            ->editColumn('updated_at', function(LuckyDrawClient $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null;
            })
            ->editColumn('type', function(LuckyDrawClient $model) {
                return $model->type;
            })
            ->addColumn('actions', function(LuckyDrawClient $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return "hidden";
                return $this->generateActionBtns($model);
            });

        return $dataTable;
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new LuckyDrawClient()); // Get the base query
        }

        $query->where('status', '!=', LuckyDrawClient::STATUS_DELETED);
        $query->where('lucky_draw_id', '=', $this->luckyDraw->id);
        $query->orderBy('updated_at', 'DESC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\LuckyDrawClient $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LuckyDrawClient $model)
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
                    ->setTableId('lucky-draw-client-table')
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
                        'responsive'    => true,
                        'autoWidth'     => false,
                        'pageLength'    => 20,
                        'lengthMenu'    => [5, 10, 20, 30, 50, 100, 200, 500],
                        'processing'    => true,
                        'stateSave'     => true,
                        'language'      => $this->getL(),
                        'initComplete' => "function () {
                            this.api().columns().every(function () {
                                var column = this;
                                var input = document.createElement('input');
                                input.placeholder = '🔍';
                                input.style.width = '100%';
                                input.className = 'form-control form-control-sm';
                                $(input).appendTo($(column.footer()).empty())
                                    .on('keyup change clear', function () {
                                        if (column.search() !== this.value) {
                                            column.search(this.value).draw();
                                        }
                                    });
                            });
                        }",
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
            Column::make('actions')
                ->title(""),
            Column::make('qrcode')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['qrcode']['desc'] ?? "Qrcode"),
            Column::make('name')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['name']['desc'] ?? "Tên"),
            Column::make('email')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['email']['desc'] ?? "Email"),
            Column::make('status')
                ->title("Trạng thái"),
            Column::make('type')
                ->title("Nhóm"),
            Column::make('updated_at')
                ->addClass('text-sm')
                ->title("Cập nhật lúc"),
        ];
    }

    protected function generateActionBtns($model)
    {
        /* button edit */
        // $buttons = view('components.btn-edit', [
        //     'route' => route('admin.clients.edit', [
        //         'client'    => $model,
        //     ]),
        //     'class' => 'btn btn-xs btn-primary mb-1',
        // ]);
        /* button delete */
        $buttons = view('components.btn-del-alert', [
            'route'     => route('admin.lucky_draw_clients.destroy', $model),
            'class'     => 'btn btn-xs btn-danger mb-1',
            'confirm'   => 'Bạn có chắc chắn muốn xoá khách hàng này?',
            'modalId'   => "client-{$model->id}",
        ]);
        return $buttons;
    }
}
