<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Checkin;
use App\Models\Event;
use App\Services\Admin\CheckinService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CheckinDataTable extends BaseDataTable
{
    public $service;
    private $event;
    private $qrcode;

    public function __construct(Event $event, ?string $qrcode = null)
    {
        $this->event = $event;
        $this->qrcode = $qrcode;
        $this->service = app(CheckinService::class);
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

        $dataTable = datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->editColumn('status', function(Checkin $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('qrcode', function(Checkin $model) {
                $btn = '<a class="" data-clipboard-target="#qrcode-'.$model->id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                if ($model->img_qrcode) {
                    $route = route('clients.view-qrcode-by-id', [
                        'id' => $model->id
                    ]);

                    $btn .= '<a target="_blank" href="'.($route ?? "#").'"><i class="fa-solid fa-qrcode"></i></a> ';
                }

                $btn .= ' <span id="qrcode-'.$model->id.'" class="fw-bold">'.$model->qrcode.'</span>';
                return $btn;
            })
            ->addColumn('email', function(Checkin $model) {
                return !empty($model->client) ? $model->client->email : null;
            })
            ->addColumn('name', function(Checkin $model) {
                return !empty($model->client) ? $model->client->name : null;
            })
            ->editColumn('updated_at', function(Checkin $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null;
            })
            ->editColumn('type', function(Checkin $model) {
                return $model->type;
            })
            ->editColumn('register_source', function(Checkin $model) {
                return $model->register_source;
            })
            ->editColumn('updated_by', function(Checkin $model) {
                return $model->updated_by ? $model->user->name : null;
            })
            ->editColumn('scan_time', function(Checkin $model) {
                return humanize_date($model->scan_time, 'H:i:s Y-m-d');
            })
            ->addColumn('user', function(Checkin $model) {
                return $model->user_id ? $model->user->username : null;
            })
            ->addColumn('actions', function(Checkin $model) {
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Checkin $model) {
                if ($model->type == Checkin::TYPE_CHECKOUT) {
                    return "table-secondary";
                }

                return null;
            });

        return $dataTable;
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Checkin()); // Get the base query
        }

        if ($this->qrcode) {
            $query->where('checkins.qrcode', $this->qrcode);
        }

        if (request('scan_type')) {
            $scanType = request('scan_type');

            if ($scanType == 'empty') {
                $query->whereHas('client', function ($q) {
                    $q->whereNull('type')->orWhere('type', '');
                });
            } else {
                $query->whereHas('client', function ($q) use ($scanType) {
                    $q->where('type', $scanType);
                });
            }
        }

        $query->where('checkins.status', '!=', Checkin::STATUS_DELETED);
        $query->where('checkins.event_id', '=', $this->event->id);
        $query->orderBy('checkins.updated_at', 'DESC');
        $query = $this->service->applyFilters($this->event->id, $query);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Checkin $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Checkin $model)
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
                    ->setTableId('checkin-table')
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
            Column::make('qrcode')
                ->addClass('text-sm')
                ->title("Qrcode"),
            Column::make('email')
                ->addClass('text-sm')
                ->title("Email"),
            Column::make('name')
                ->addClass('text-sm')
                ->title("Name"),
            Column::make('type')
                ->title("Loại"),
            Column::make('scan_time')
                ->title("Thời gian check"),
            Column::make('user')
                ->addClass('text-sm')
                ->title("Checkin bởi"),
            // Column::make('updated_at')
            //     ->addClass('text-sm')
            //     ->title("Cập nhật lúc"),
        ];
    }

    protected function generateActionBtns($model)
    {

    }
}
