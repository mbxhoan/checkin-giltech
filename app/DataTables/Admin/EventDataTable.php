<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Helpers\Helper;
use App\Models\Client;
use App\Models\Company;
use App\Models\Event;
use App\Models\ImpexpFile;
use App\Models\User;
use App\Services\Admin\EventService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class EventDataTable extends BaseDataTable
{
    public $service;

    public function __construct()
    {
        $this->service = app(EventService::class);
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
            ->editColumn('status', function(Event $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('code', function(Event $model) {
                $route = route('admin.events.edit', $model);
                $btns = '<span class="fw-bold" id="code-'.$model->id.'"><b>'.$model->code.'</b></span>';
                $btns .= '<a class="" data-clipboard-target="#code-'.$model->id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                return $btns;
                return '<a href="'.$route.'"><b>'.$model->code.'</b> <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('name', function(Event $model) {
                if (!auth()->user()->isAdmin()) {
                    return $model->name;
                }

                $route = route('admin.events.edit', $model);
                return '<a href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit fa-fw"></i></a>';
            })
            ->editColumn('updated_by', function(Event $model) {
                return $model->user->name;
            })
            ->editColumn('updated_at', function(Event $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('run_dates', function(Event $model) {
                if ($model->from_date == $model->to_date) {
                    return humanize_date($model->from_date, 'd/m/Y');
                }

                return humanize_date($model->from_date, 'd/m/Y').'<br>'.humanize_date($model->to_date, 'd/m/Y');
            })
            ->addColumn('province', function(Event $model) {
                return $model->province ? $model->province->name : null;
            })
            ->addColumn('clients_count', function(Event $model) {
                $route = route('admin.clients.index', [
                    'event' => $model
                ]);

                return '<a href="'.$route.'"><i class="fa-solid fa-users"></i> <b>'.$model->clients_count.'</b></a>';
            })
            ->addColumn('checkins_count', function(Event $model) {
                $route = route('admin.checkins.index', [
                    'event' => $model
                ]);

                $totalCheckedIn = $this->service->middleware_client()->countClientByCheck($model->code, 'CHECKIN');

                /* progress import */
                $file = $this->service->imp_exp_file()->findByAttributes([
                    'event_id'  => $model->id,
                ], [], [], [
                    'id'        => 'DESC'
                ]);

                if (!empty($file) && ($file->status == ImpexpFile::STATUS_NEW)) {
                    $html = view('components._progress', [
                        'total'     => $file->total_record,
                        'completed' => $file->total_record_before,
                        'dataTime'  => 5, // giây
                        'dataEle'   => '#progress',
                        'dataUrl'   => route('admin.imp_exp_files.progress', [
                            'imp_exp_file' => $file,
                        ]),
                    ]);
                    $routeImport = route('admin.clients.import', $model);
                    $html = '<a href="'.$routeImport.'"><i class="fa-solid fa-sm fa-upload"></i> <b> '.$html.'</b></a>';
                }
                /* end */

                return '<a href="'.$route.'"><i class="fa-solid fa-sm fa-check"></i> <b>'.$totalCheckedIn.'</b></a>'.($html ?? null);
            })
            ->addColumn('checkouts_count', function(Event $model) {
                $route = route('admin.checkins.index', [
                    'event' => $model
                ]);

                $totalCheckedOut = $this->service->middleware_client()->countClientByCheck($model->code, 'CHECKOUT');

                /* progress import */
                $file = $this->service->imp_exp_file()->findByAttributes([
                    'event_id'  => $model->id,
                ], [], [], [
                    'id'        => 'DESC'
                ]);

                if (!empty($file) && ($file->status == ImpexpFile::STATUS_NEW)) {
                    $html = view('components._progress', [
                        'total'     => $file->total_record,
                        'completed' => $file->total_record_before,
                        'dataTime'  => 5, // giây
                        'dataEle'   => '#progress',
                        'dataUrl'   => route('admin.imp_exp_files.progress', [
                            'imp_exp_file' => $file,
                        ]),
                    ]);
                    $routeImport = route('admin.clients.import', $model);
                    $html = '<a href="'.$routeImport.'"><i class="fa-solid fa-sm fa-upload"></i> <b> '.$html.'</b></a>';
                }
                /* end */

                return '<a href="'.$route.'"><i class="fa-solid fa-sm fa-check"></i> <b>'.$totalCheckedOut.'</b></a>'.($html ?? null);
            })
            ->addColumn('users_count', function(Event $model) {
                if (!auth()->user()->isAdmin()) {
                    return $model->users_count;
                }

                $route = route('admin.users.index', [
                    'event_id'      => $model->id
                ]);

                $btns = '<a href="'.$route.'"><i class="fa-solid fa-users"></i> <b>'.$model->users_count.'</b></a>';

                if (auth()->user()->isSysAdmin()) {
                    $routeCreateUser = route('admin.users.create', [
                        'company_id'    => $model->company_id,
                        'event_id'      => $model->id
                    ]);
                } else {
                    $routeCreateUser = route('admin.users.create', [
                        'event_id'      => $model->id
                    ]);
                }

                $btns .= '<a target="_blank" href="'.$routeCreateUser.'"><i class="fa-regular fa-plus-square fa-fw"></i></a>';
                return $btns;
            })
            ->addColumn('actions', function(Event $model) {
                if (!auth()->user()->isAdmin()) {
                    return null;
                }

                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Event $model) {
                if ($model->status == Event::STATUS_DONE) {
                    return " opacity-25";
                }

                if ($model->status == Event::STATUS_INACTIVE) {
                    return "table-secondary opacity-25";
                }

                if (Helper::isTodayInRange($model->from_date, $model->to_date)) {
                    return "";
                }

                return '';
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Event()); // Get the base query
        }

        if (!auth()->user()->isSysAdmin()) {
            $query->where('company_id', auth()->user()->company->id);
        } else {
            if (request()->filled('company_id')) {
                $attributes['company_id'] = request()->input('company_id');
            }
        }

        if (!auth()->user()->isAdmin()) {
            $query->where('id', auth()->user()->event_id);
        }

        $query->where('status', '!=', Event::STATUS_DELETED);
        $query->orderBy('status', 'ASC');
        $query->orderBy('updated_at', 'DESC');

        /* lọc */
        if (request()->filled('province_id')) {
            $attributes['province_id'] = request()->input('province_id');
        }

        if (request()->filled('status')) {
            $attributes['status'] = request()->input('status');
        } else {
            $query->where('status', '!=', Event::STATUS_DONE);
        }

        $dateField = request()->input('field_date');

        if ($dateField) {
            if (in_array($dateField, [
                'run_date'
            ])) {
                if (request()->filled('from_date')) {
                    $query->whereDate('from_date', '>=', request()->from_date);
                }

                if (request()->filled('to_date')) {
                    $query->whereDate('to_date', '<=', request()->to_date);
                }
            } else {
                if (request()->filled('from_date')) {
                    $query->whereDate(request()->input('field_date'), '>=', request()->input('from_date'));
                }

                if (request()->filled('to_date')) {
                    $query->whereDate(request()->input('field_date'), '<=', request()->input('to_date'));
                }
            }
        }

        if (isset($attributes) && count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Event $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Event $model)
    {
        return $model->newQuery()
            ->withCount([
                'clients as clients_count' => function ($query) {
                    $query->where('status', '!=', Client::STATUS_DELETED);
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
                    ->setTableId('event-table')
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
                ->title("Mã"),
            Column::make('name')
                ->title("Tên"),
            Column::make('clients_count')
                ->title("Khách mời"),
            Column::make('checkins_count')
                ->title("Checkin"),
            Column::make('checkouts_count')
                ->title("Checkout"),
            Column::make('users_count')
                ->title("User(s)"),
            Column::make('run_dates')
                ->title("Diễn ra"),
            Column::make('province')
                ->title("Tỉnh/Thành"),
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
            'route' => route('admin.events.edit', $model),
            'class' => 'btn btn-sm btn-primary mb-2',
        ]);

        $buttons .= '<a class="btn btn-sm btn-primary mb-2 me-2" href="'.route('admin.checkins.config', $model).'">
            Checkin
        </a>';

        if (auth()->user()->isSysAdmin()) {
            $companys = $this->service->company()->getListByAttributes([
                'status'    => [
                    Company::STATUS_ACTIVE,
                ],
            ]);
        } else {
            $company = auth()->user()->company;
        }

        $buttons .= view('admin.events._btn-clone', [
            'model'                 => $model,
            'route'                 => route('admin.events.clone', $model),
            'class'                 => 'btn btn-sm btn-primary mb-2',
            'confirm'               => "Bạn có chắc chắn muốn nhân bản sự kiện này?",
            'text'                  => 'Nhân bản',
            'icon'                  => '<i class="fa-solid fa-copy"></i>',
            'modalId'               => "event-clone-{$model->id}",
            'label'                 => 'VUI LÒNG NHẬP <b>"COPY"</b> ĐỂ XÁC NHẬN NHÂN BẢN',
            'companyArray'          => !empty($companys) ? $companys->mapWithKeys(function ($company) {
                return [$company->id => "{$company->code} - {$company->name}"];
            })->toArray() : [],
            'company'               => $company ?? null,
        ]);

        if ($model->status == Event::STATUS_NEW) {
            if (empty($model->clients) || !$model->clients->count()) {
                // $buttons .= view('components.btn-del', [
                //     'route' => route('admin.events.destroy', $model),
                //     'class' => 'btn btn-sm btn-danger',
                // ]);
            }
        }

        return $buttons;
    }
}
