<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\LandingPage;
use App\Models\Event;
use App\Services\Admin\LandingPageService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LandingPageDataTable extends BaseDataTable
{
    public $service;
    private $event = null;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
        $this->service = app(LandingPageService::class);
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
            ->editColumn('status', function(LandingPage $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('event_id', function(LandingPage $model) {
                $route = route('admin.events.edit', $model->event);
                $text = '<span class="fw-bold text-sm" id="code-'.$model->event_id.'"><b>'.$model->event->code.'</b></span>';
                $btnCopy = '<a class="" data-clipboard-target="#code-'.$model->event_id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return '<a href="'.$route.'"><b>'.$text.'</b></a> '.$btnCopy;
            })
            ->editColumn('languages', function(LandingPage $model) {
                $languages = $model->getLanguages();
                $text = "";

                foreach ($languages as $language) {
                    $text .= '<div class="mb-1"><span class="text-xs fw-bold"><img src="'.asset("storage/{$language->icon_path}").'" width="20" alt=""/> '.$language->name.'</span></div>';
                }

                return $text;
            })
            ->editColumn('slug', function(LandingPage $model) {
                $route = route('admin.landing_pages.edit', $model);
                $buttons = '<a class="fst-italic" href="'.$route.'" id="lp-link-'.$model->id.'">'.$model->getRegisterUrl().'</a> ';
                $buttons .= '<a target="_blank" class="fst-italic" href="'.$model->getRegisterUrl().'" id=""><i class="fa-sm fa-solid fa-arrow-up-right-from-square"></i></a> ';
                $buttons .= '<button type="button" class="input-group-text btn btn-sm text-primary p-0" data-clipboard-target="#lp-link-'.$model->id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </button>';
                return $buttons;
            })
            ->editColumn('updated_by', function(LandingPage $model) {
                return !empty($model->updated_by) ? $model->user->name : null;
            })
            ->editColumn('updated_at', function(LandingPage $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('access_log', function(LandingPage $model) {
                return $model->accesses->count();
            })
            ->addColumn('actions', function(LandingPage $model) {
                return $this->generateActionBtns($model);
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new LandingPage()); // Get the base query
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

        $query->where('status', '!=', LandingPage::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        // $query = $this->service->applyFilters($query);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\LandingPage $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(LandingPage $model)
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
                    ->setTableId('landing_pages-table')
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
            Column::make('slug')
                ->title("Link"),
            Column::make('event_id')
                ->title("Sự kiện"),
            Column::make('access_log')
                ->title("Lượng truy cập")
                ->addClass('text-center'),
            Column::make('languages')
                ->title("Ngôn ngữ")
                ->width(100)
                ->addClass(''),
            Column::make('status')
                ->title("Trạng thái"),
            Column::make('updated_by')
                ->title("Cập nhật bởi"),
            Column::make('updated_at')
                ->title("Cập nhật lúc"),
            Column::make('actions')
                ->title(""),
        ];
    }

    protected function generateActionBtns($model)
    {
        $buttons = view('components.btn-edit', [
            'route' => route('admin.landing_pages.edit', [
                'event'         => $model->event,
                'landing_page'  => $model
            ]),
            'class' => 'btn btn-sm btn-primary',
        ]);

        return $buttons;
    }
}
