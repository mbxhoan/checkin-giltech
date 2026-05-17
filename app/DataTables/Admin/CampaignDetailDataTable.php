<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Campaign;
use App\Models\CampaignDetail;
use App\Services\Admin\CampaignDetailService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CampaignDetailDataTable extends BaseDataTable
{
    private $campaign;

    public function __construct(?Campaign $campaign)
    {
        $this->campaign = $campaign;
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
            ->addColumn('checkbox', function(CampaignDetail $model) {
                return '<input type="checkbox" class="form-check-input" name="ids[]" value="'.$model->id.'" checked>';
            })
            ->editColumn('name', function(CampaignDetail $model) {
                $buttons = '<a href="" data-bs-toggle="modal" data-bs-target="#'.$model->id.'Modal">
                    <i class="fa-solid fa-circle-info"></i>
                </a>';
                $buttons .= ' <a
                    data-id="'.$model->id.'"
                    data-field="name"
                    href="#"
                    class="text-primary btn-quick-edit"><i class="fa-solid fa-edit"></i></a>';

                $fields = array_merge([
                    'name'          => $model->name,
                    'email'         => $model->email,
                    'qrcode'        => $model->qrcode,
                    'img_qrcode'    => route('clients.view-qrcode-by-id', [
                        'id'        => $model->id
                    ]),
                    'document_pdf'  => route('clients.view-document-pdf', [
                        'clientId'  => $model->id
                    ]),
                ], json_decode($model->custom_fields, true) ?? []);

                $modal = view('admin.emails._modal-info', [
                    'modalId'       => "{$model->id}Modal",
                    'data'          => collect($fields),
                ]);

                $text = '<span id="name-'.$model->id.'">'.$model->name.'</span>';
                return $text.' '.$buttons.' '.$modal;
            })
            ->editColumn('email', function(CampaignDetail $model) {
                $route = route('campaign_details.view-email', $model);
                $btns = '<span id="email-'.$model->id.'">'.$model->email.'</span>';
                $btns .= ' <a href="'.$route.'" target="_blank" class="text-primary"><i class="fa-solid fa-eye"></i></a>';
                $btns .= ' <a
                    data-id="'.$model->id.'"
                    data-field="email"
                    href="#"
                    class="text-primary btn-quick-edit"><i class="fa-solid fa-edit"></i></a>';

                return $btns;
            })
            ->addColumn('actions', function(CampaignDetail $model) {
                return $this->generateActionBtns($model);
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new CampaignDetail()); // Get the base query
        }

        $query->where('campaign_id', '=', $this->campaign->id);
        $query->orderBy('updated_at', 'DESC');
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CampaignDetail $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CampaignDetail $model)
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
                    ->setTableId('campaign-details-table')
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
                        'pageLength'    => 20,
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
            Column::make('checkbox')
                ->title("")
                ->addClass('text-xs align-self-center'),
            // Column::make('qrcode')
            //     ->title("Qrcode")
            //     ->addClass('text-xs align-self-center'),
            Column::make('name')
                // ->width(100)
                ->title("Họ tên")
                ->addClass('text-xs align-self-center'),
            Column::make('email')
                ->title("Email")
                ->addClass('text-xs align-self-center'),
            // Column::make('actions')
            //     ->title("")
            //      ->addClass('text-xs align-self-center')'),
        ];
    }

    protected function generateActionBtns($model)
    {
        // $buttons = view('components.btn-edit', [
        //     'route' => route('admin.campaigns.edit', $model),
        //     'class' => 'btn btn-sm btn-primary',
        // ]);

        return $buttons ?? null;
    }
}
