<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Card;
use App\Models\Event;
use App\Services\Admin\CardService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CardDataTable extends BaseDataTable
{
    public $service;
    private $event = null;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
        $this->service = app(CardService::class);
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
            ->editColumn('status', function(Card $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('event_id', function(Card $model) {
                $route = route('admin.events.edit', $model->event);
                $text = '<span class="fw-bold text-sm" id="code-'.$model->event_id.'"><b>'.$model->event->code.'</b></span>';
                $btnCopy = '<a class="" data-clipboard-target="#code-'.$model->event_id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return '<a href="'.$route.'"><b>'.$text.'</b></a> '.$btnCopy;
            })
            ->addColumn('thumb', function(Card $model) {
                if ($model->backgroundUrl) {
                    return '<div><a href="'.$model->backgroundUrl->getUrl().'" target="_blank" title="Background" class="">
                        <img src="'.$model->backgroundUrl->getUrl().'" alt="'.$model->code.'" width="100px">
                    </a></div>';
                }
            })
             ->editColumn('code', function(Card $model) {
                return '<a href="'.route('admin.cards.edit', $model).'" target="">'.$model->code.' <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('client_type', function(Card $model) {
                return $model->client_type ?? "Tất cả";
            })
            ->editColumn('updated_at', function(Card $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('progress', function(Card $model) {
                $totalClients = $this->service->client()->getListByAttributes(array_filter([
                    'event_id'  => $model->event_id,
                    'type'      => $model->client_type,
                ]));

                if ($totalClients->count() == 0) {
                    return '<span class="text-xs fst-italic">Chưa có</span>';
                }

                // $generatedClients = (clone $totalClients)->where('document_pdf', '!=', null);
                $generatedClients = $this->service->getGenerateFilesCount($model);
                $child = view('components._progress', [
                    'completed'     => $generatedClients,
                    'total'         => $totalClients->count(),
                    'dataTime'      => 3, // giây
                    'dataEle'       => '#progress',
                    'dataUrl'       => route('admin.cards.progress', $model),
                ]);

                return '<a href="'.route('admin.cards.edit', $model).'" class="" title="Báo cáo tiến độ">'.$child.'</a>';
            })
            ->addColumn('actions', function(Card $model) {
                return $this->generateActionBtns($model);
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Card()); // Get the base query
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

        $query->where('status', '!=', Card::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        // $query = $this->service->applyFilters($query);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Card $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Card $model)
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
                    ->setTableId('cards-table')
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
            Column::make('code')
                ->title("Thông tin"),
            Column::make('thumb')
                ->title("background"),
            Column::make('event_id')
                ->title("Sự kiện"),
            Column::make('client_type')
                ->title("Nhóm khách"),
            Column::make('progress')
                ->title("Tiến trình tạo"),
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
            'route' => route('admin.cards.edit', $model),
            'class' => 'btn btn-sm btn-primary',
        ]);

        return $buttons;
    }
}
