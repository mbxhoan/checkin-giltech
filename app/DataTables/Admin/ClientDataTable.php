<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Services\Admin\ClientService;
use App\Services\Videc\TicketAnalyticsService;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientDataTable extends BaseDataTable
{
    public $service;
    private $event;
    private $customFieldTemplates = [];
    private TicketAnalyticsService $ticketAnalyticsService;
    private $cachedEventLabels = null;
    private $cachedEventCampaigns = null;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->service = app(ClientService::class);
        $this->ticketAnalyticsService = app(TicketAnalyticsService::class);
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();
        $this->cachedEventLabels = $event->labels;
        $this->cachedEventCampaigns = $event->campaigns ?? collect();
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
        $limitedClients = $this->event->company->limited_clients;

        $dataTable = datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->addIndexColumn()
            ->editColumn('status', function(Client $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('qrcode', function(Client $model) use ($limitedClients) {
                $btn = '<a class="" data-clipboard-target="#qrcode-'.$model->id.'">
                    <i class="fa-regular fa-clipboard"></i>
                </a>';

                if ($model->img_qrcode) {
                    $route = route('clients.view-qrcode-by-id', [
                        'id' => $model->id
                    ]);

                    $btn .= '<a target="_blank" href="'.($route).'"><i class="fa-solid fa-qrcode"></i></a> ';
                } else {
                    $route = route('clients.generate-qrcode-by-id', [
                        'id' => $model->id
                    ]);

                    $btn .= '<a target="_blank" href="'.($route).'"><i class="fa-solid fa-plus"></i></a> ';
                }

                if ($model->document_pdf) {
                    $route = route('clients.view-document-pdf', [
                        'clientId' => $model->id
                    ]);
                    $btn .= '<a target="_blank" href="'.($route).'"><i class="fa-solid fa-image"></i></a> ';
                }

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;
                $btn .= '<div id="qrcode-'.$model->id.'" class="opacity-0 fw-bold">'.$model->qrcode.'</div>';
                return $btn;
            })
            ->editColumn('name', function(Client $model) use ($limitedClients) {
                $route = route('admin.clients.edit', [
                    'client'    => $model,
                ]);

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;
                return '<a href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit"></i></a>';
            })
            // ->editColumn('updated_at', function(Client $model) {
            //     return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null;
            // })
            ->editColumn('type', function(Client $model) {
                return $model->type;
            })
            ->editColumn('register_source', function(Client $model) {
                return $model->register_source;
            })
            ->editColumn('updated_by', function(Client $model) {
                return collect([
                    $model->updated_by ? optional($model->user)->name : null,
                    $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null,
                ])->filter()->implode('<br>');
            })
            ->editColumn('first_checkin_at', function (Client $model) {
                return $this->formatScanTime($model->first_checkin_at);
            })
            ->editColumn('first_checkout_at', function (Client $model) {
                return $this->formatScanTime($model->first_checkout_at);
            })
            ->addColumn('checkin_checkout_duration', function (Client $model) {
                return $this->formatCheckinCheckoutDuration($model->first_checkin_at, $model->first_checkout_at);
            })
            ->addColumn('actions', function(Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return "hidden";
                return $this->generateActionBtns($model, $this->cachedEventLabels, $this->cachedEventCampaigns);
            })
            ->setRowClass(function (Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;

                if (!empty($model->first_checkin_at)) {
                    return "table-secondary";
                }

                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) {
                    // return "table-dark";
                }

                return null;
            });

        if ($this->event->code === 'videc-2026') {
            $dataTable = $dataTable->addColumn('ticket_summary', function (Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) {
                    return null;
                }

                return $this->renderTicketSummary($model);
            });
        }

        // $dataTable = $dataTable->filterColumn('name', function($query, $keyword) {
        //     $query->where('name', 'like', "%{$keyword}%");
        // });

        foreach ($this->customFieldTemplates as $templateName => $templateDesc) {
            $fieldName = strtolower($templateName);

            // Add the custom column
            $dataTable = $dataTable->addColumn($fieldName, function(Client $model) use ($templateDesc, $limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;
                return $model->getCustomFieldValue($templateDesc, false);
            });

            // Make the column searchableimport
            $dataTable = $dataTable->filterColumn($fieldName, function($query, $keyword) use ($fieldName) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"'))) LIKE ?", ["%".strtolower($keyword)."%"]);
            });
        }

        return $dataTable;
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Client()); // Get the base query
        }

        $query->where('status', '!=', Client::STATUS_DELETED);
        $query->where('event_id', '=', $this->event->id);
        $query->orderBy('updated_at', 'DESC');
        // $query->orderBy('id', 'DESC');
        $query = $this->service->applyFilters($query);

        $query = $this->service->applyCustomFieldFilters($query, $this->event->id);
        // $query->take(50);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Client $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Client $model)
    {
        return $model->newQuery()
            ->with(['user:id,name'])
            ->select('clients.*')
            ->selectSub(
                Checkin::query()
                    ->selectRaw('MIN(scan_time)')
                    ->whereColumn('checkins.event_id', 'clients.event_id')
                    ->whereColumn('checkins.qrcode', 'clients.qrcode')
                    ->where('checkins.type', Checkin::TYPE_CHECKIN)
                    ->where('checkins.status', '!=', Checkin::STATUS_DELETED),
                'first_checkin_at'
            )
            ->selectSub(
                Checkin::query()
                    ->selectRaw('MIN(scan_time)')
                    ->whereColumn('checkins.event_id', 'clients.event_id')
                    ->whereColumn('checkins.qrcode', 'clients.qrcode')
                    ->where('checkins.type', Checkin::TYPE_CHECKOUT)
                    ->where('checkins.status', '!=', Checkin::STATUS_DELETED),
                'first_checkout_at'
            );
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('client-table')
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
        $fullCustomFieldTemplates = $this->event->getCustomFieldTemplates(true);

        $columns = [
            Column::make('actions')
                ->title(""),
            Column::make('qrcode')
                ->addClass('text-xs')
                ->width(10)
                ->sortable(true)
                ->title($fullCustomFieldTemplates['qrcode']['desc'] ?? "Qrcode"),
            Column::make('ref_id')
                ->addClass('text-sm')
                ->title("REF ID"),
            Column::make('status')
                ->title("Trạng thái")
                ->sortable(true),
            Column::make('type')
                ->title("Nhóm")
                ->sortable(true),
            Column::make('register_source')
                ->title("Nguồn đăng ký")
                ->sortable(true),
            Column::make('updated_by')
                ->addClass('text-sm')
                ->title("Cập nhật")
                ->sortable(true),
            Column::make('name')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['name']['desc'] ?? "Tên"),
            Column::make('email')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['email']['desc'] ?? "Email"),
            Column::make('first_checkin_at')
                ->addClass('text-sm')
                ->title("Checkin đầu")
                ->searchable(false)
                ->orderable(false),
            Column::make('first_checkout_at')
                ->addClass('text-sm')
                ->title("Checkout đầu")
                ->searchable(false)
                ->orderable(false),
            Column::make('checkin_checkout_duration')
                ->addClass('text-sm')
                ->title("Tổng TG checkin-checkout")
                ->searchable(false)
                ->orderable(false),
            // Column::make('updated_at')
            //     ->addClass('text-sm')
            //     ->title("Cập nhật lúc")
            //     ->sortable(true),
        ];

        if ($this->event->code === 'videc-2026') {
            $columns[] = Column::make('ticket_summary')
                ->addClass('text-sm')
                ->title("Vé")
                ->searchable(false)
                ->orderable(false);
        }

        foreach ($this->customFieldTemplates as $templateName => $templateAttr) {
            $customColumns[] = Column::make(strtolower($templateName))
                ->addClass("{$templateName} text-sm all")
                ->title($templateAttr['desc'] ?? strtoupper($templateName))
                ->searchable(true)
                ->orderable(true);
        }

        return  array_merge($columns, $customColumns ?? []);
    }

    private function renderTicketSummary(Client $client): string
    {
        if ($this->event->code !== 'videc-2026') {
            return '<span class="text-muted">-</span>';
        }

        $ticketHistory = $this->ticketAnalyticsService->clientHistory($client);

        return view('admin.clients._ticket-summary', [
            'ticketHistory' => $ticketHistory,
        ])->render();
    }

    private function formatScanTime($scanTime): ?string
    {
        if (empty($scanTime)) {
            return null;
        }

        return Carbon::parse($scanTime)->format('d/m/Y H:i:s');
    }

    private function formatCheckinCheckoutDuration($firstCheckinAt, $firstCheckoutAt): ?string
    {
        if (empty($firstCheckinAt) || empty($firstCheckoutAt)) {
            return null;
        }

        $checkinAt = Carbon::parse($firstCheckinAt);
        $checkoutAt = Carbon::parse($firstCheckoutAt);
        $seconds = $checkinAt->diffInSeconds($checkoutAt, false);
        $prefix = $seconds < 0 ? '-' : '';
        $seconds = abs($seconds);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return $prefix.sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    protected function generateActionBtns($model, $labels = null, $campaigns = null)
    {
        $labels = $labels ?? collect();
        $campaigns = $campaigns ?? collect();

        /* button edit */
        $buttons = view('components.btn-edit', [
            'route' => route('admin.clients.edit', [
                'client'    => $model,
            ]),
            'class' => 'btn btn-xs btn-primary mb-1',
        ]);
        /* button delete */
        $buttons .= view('components.btn-del-alert', [
            'route'     => route('admin.clients.destroy', $model),
            'class'     => 'btn btn-xs btn-danger mb-1',
            'confirm'   => 'Bạn có chắc chắn muốn xoá khách hàng này?',
            'modalId'   => "client-{$model->id}",
        ]);
        /* button print */
        $label = $labels->first();
        if ($label && in_array($label->status, [
            Label::STATUS_NEW,
            Label::STATUS_ACTIVE
        ])) {
            $buttons .= '
                <a class="btn btn-xs btn-warning mb-1 btn-toggle-modal"
                    data-modal_id="modalLabelPrint-'.$model->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelPrint-'.$model->id.'"
                >
                    <i class="fa-solid fa-print"></i>
                </a>';
            $buttons .= view('admin.clients._modal-print', [
                'modalId'           => "modalLabelPrint-{$model->id}",
                'title'             => "In tem",
                'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                'modalBodyClass'    => 'text-sm',
                'labels'            => $labels,
                'label'             => $label,
                'labelDetails'      => $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null,
                'event'             => $this->event,
                'client'            => $model,
            ]);
        }
        /* button checkin */
        $buttons .= view('components.btn-checkin', [
            'route' => route('admin.checkins.checkin'),
            'class' => 'btn btn-xs btn-success mb-1',
            'model' => $model,
            'text'  => 'Checkin',
            'type'  => \App\Models\Checkin::TYPE_CHECKIN,
        ]);
        $buttons .= view('components.btn-checkin', [
            'route' => route('admin.checkins.checkin'),
            'class' => 'btn btn-xs btn-warning mb-1',
            'model' => $model,
            'text'  => 'Checkout',
            'type'  => \App\Models\Checkin::TYPE_CHECKOUT,
        ]);

        /* button send mail */
        if ($campaigns->count()) {
            $buttons .= '
                <a class="btn btn-xs btn-info mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelSendMail-'.$model->id.'">
                    <i class="fa-solid fa-paper-plane"></i>
                </a>';

            $buttons .= view('admin.clients._modal-send-mail', [
                'modalId'           => "modalLabelSendMail-{$model->id}",
                'title'             => "Gửi mail",
                'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                'modalBodyClass'    => 'text-sm',
                'campaigns'         => $campaigns,
                'event'             => $this->event,
                'client'            => $model,
                'display'           => true,
            ]);
        }

        return $buttons;
    }
}
