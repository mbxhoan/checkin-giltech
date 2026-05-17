<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Client;
use App\Models\Campaign;
use App\Models\Email;
use App\Models\Event;
use App\Services\Admin\CampaignService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CampaignDataTable extends BaseDataTable
{
    public $service;
    private $event;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
        $this->service = app(CampaignService::class);
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
            ->editColumn('status', function(Campaign $model) {
                $completed = $model->getEmails(Email::STATUS_SENT)->count();
                $total = $model->getEmails([
                    Email::STATUS_NEW,
                    Email::STATUS_WAITING,
                    Email::STATUS_SENT,
                ])->count();

                if ($completed == $total && $total > 0) {
                    $model->status = Campaign::STATUS_COMPLETED;
                    $model->saveQuietly();
                }

                return '<label class="btn btn-xs '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('event_id', function(Campaign $model) {
                $route = route('admin.events.edit', $model->event);
                $text = '<span class="fw-bold text-sm" id="code-'.$model->event_id.'"><b>'.$model->event->code.'</b></span>';
                $btnCopy = '<a class="" data-clipboard-target="#code-'.$model->event_id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return '<a href="'.$route.'"><b>'.$text.'</b></a> '.$btnCopy;
            })
            ->editColumn('name', function(Campaign $model) {
                $route = route('admin.campaigns.edit', $model);
                return '<a href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('template_id', function(Campaign $model) {
                $route = route('admin.email_templates.edit-postmark-template', $model->template_id);
                return '<a href="'.$route.'" class="fw-bold fst-italic">#'.$model->template_id.'</a>';
            })
            ->editColumn('from_email', function(Campaign $model) {
                return "<div>{$model->from_email}</div><div>{$model->from_name}</div>";
            })
            ->editColumn('updated_by', function(Campaign $model) {
                return $model->updated_by ? $model->user->name : null;
            })
            ->editColumn('updated_at', function(Campaign $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('progress', function(Campaign $model) {
                $total = $model->getEmails([
                    Email::STATUS_NEW,
                    Email::STATUS_WAITING,
                    Email::STATUS_SENT,
                ])->count();

                if ($total == 0) {
                    return '<span class="text-xs fst-italic">Chưa có</span>';
                }

                $route = route('admin.campaigns.history', $model);
                $child = view('components._progress', [
                    'completed' => $model->getEmails(Email::STATUS_SENT)->count(),
                    // 'total'     => $model->getEmails(Email::STATUS_WAITING)->count(),
                    'total'     => $total,
                ]);

                $progress = '<a href="'.$route.'" class="" title="Báo cáo gửi mail">'.$child.'</a>';
                $btnExport = '<a href="'.route('admin.emails.export-report', [
                        'event'         => $model->event,
                        'campaign_id'   => $model->id,
                    ]).'"
                    class="btn btn-xs btn-success mt-2"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    '.__('imports.export').'
                </a>';
                return $progress.$btnExport;
            })
            ->addColumn('emails_count', function(Campaign $model) {
                // $route = route('admin.clients.index', [
                //     'event' => $model
                // ]);

                $completed = $model->getEmails(Email::STATUS_SENT)->count();
                    // 'total'     => $model->getEmails(Email::STATUS_WAITING)->count(),
                $total = $model->getEmails([
                    Email::STATUS_NEW,
                    Email::STATUS_WAITING,
                    Email::STATUS_SENT,
                ])->count();

                return "{$completed}/{$total}";

                // return '<a href="'.$route.'"><i class="fa-solid fa-paper-plane"></i> <b>'.$model->emails_count.'</b></a>';
            })
            ->addColumn('actions', function(Campaign $model) {
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Campaign $model) {
                if ($model->status == Campaign::STATUS_COMPLETED) {
                    return "table-success";
                }

                return '';
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Campaign()); // Get the base query
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

        $query->where('status', '!=', Campaign::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        // $query = $this->service->applyFilters($query);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Campaign $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Campaign $model)
    {
        return $model->newQuery()
            ->withCount([
                'campaign_details as campaign_details_count' => function ($query) {
                    $query->where('status', '!=', Campaign::STATUS_DELETED);
                }
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
                    ->setTableId('campaigns-table')
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
                ->title("Campaign"),
            Column::make('event_id')
                ->searchable(true)
                ->title("Sự kiện"),
            Column::make('template_id')
                ->title("Nội dung mail"),
            Column::make('from_email')
                ->title("Người gửi")
                ->addClass('text-sm'),
            Column::make('emails_count')
                ->title("Email(s)"),
            Column::make('progress')
                ->title("Tiến trình gửi"),
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
            'route' => route('admin.campaigns.edit', $model),
            'class' => 'btn btn-sm btn-primary mb-2',
        ]);

        $buttons .= view('components.btn-alert', [
            'route'     => route('admin.campaigns.clone', $model),
            'class'     => 'btn btn-sm btn-primary mb-2',
            'confirm'   => "Bạn có chắc chắn muốn nhân bản campaign này?",
            'text'      => 'Nhân bản',
            'icon'      => '<i class="fa-solid fa-copy"></i>',
            'modalId'   => "campaign-clone-{$model->id}",
            'label'     => 'VUI LÒNG NHẬP <b>"COPY"</b> ĐỂ XÁC NHẬN NHÂN BẢN',
        ]);

        if ($model->status == Campaign::STATUS_NEW) {
            $buttons .= view('components.btn-del', [
                'route' => route('admin.campaigns.destroy', $model),
                'class' => 'btn btn-sm btn-danger mb-2',
            ]);
        }

        return $buttons;
    }
}
