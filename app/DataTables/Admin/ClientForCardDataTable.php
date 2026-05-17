<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Card;
use App\Models\Client;
use App\Models\Event;
use App\Services\Admin\ClientService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientForCardDataTable extends BaseDataTable
{
    public $service;
    private $event;
    private $card;
    private $filters = [];
    private $customFieldTemplates = [];

    public function __construct(Event $event, Card $card, array $filters = [])
    {
        $this->event = $event;
        $this->card = $card;
        $this->filters = $filters;
        $this->service = app(ClientService::class);
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();
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

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;

                $btn .= ' <span id="qrcode-'.$model->id.'" class="fw-bold">'.$model->qrcode.'</span>';
                return $btn;
            })
            ->editColumn('name', function(Client $model) use ($limitedClients) {
                $route = route('admin.clients.edit', [
                    'client'    => $model,
                ]);

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;

                return '<a target="_blank" href="'.$route.'"><b>'.$model->name.'</b> <i class="fa-solid fa-edit"></i></a>';
            })
            ->addColumn('actions', function(Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return "hidden";
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Client $model) use ($limitedClients) {
                // Use the DT_RowIndex column to determine the row index
                static $rowIndex = 0;
                $rowIndex++;

                if ($model->findCheckin()) {
                    return "table-secondary";
                }

                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) {
                    // return "table-dark"; // Add the `table-dark` class for rows beyond the 20th
                }

                return null;
            });

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
        // $query = $this->service->applyFilters($query);

        if (isset($this->filters) && count($this->filters)) {
            foreach ($this->filters as $key => $value) {
                $query->where($key, $value);
            }
        }

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
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['qrcode']['desc'] ?? "Qrcode"),
            Column::make('name')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['name']['desc'] ?? "Tên"),
            Column::make('email')
                ->addClass('text-sm')
                ->title($fullCustomFieldTemplates['email']['desc'] ?? "Email"),
            Column::make('type')
                ->title("Nhóm"),
        ];

        foreach ($this->customFieldTemplates as $templateName => $templateAttr) {
            $customColumns[] = Column::make(strtolower($templateName))
                ->addClass("{$templateName} text-sm all")
                ->title($templateAttr['desc'] ?? strtoupper($templateName))
                ->searchable(true)
                ->orderable(true);
        }

        return  array_merge($columns, $customColumns ?? []);
    }

    protected function generateActionBtns($model)
    {
        $class = "text-secondary";
        $route = route('clients.view-card', [
            'cardId'    => $this->card->id,
            'clientId'  => $model->id,
        ]);

        if ($model->document_pdf) {
            $class = "text-primary";
        }

        $buttons = '<a href="'.$route.'" class="'.$class.'" target="_blank"><i class="fa-solid fa-eye"></i></a>';
        return $buttons;
    }
}
