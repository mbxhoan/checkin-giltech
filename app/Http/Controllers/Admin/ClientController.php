<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CheckinDataTable;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\Admin\ClientDataTable;
use App\Exports\Clients\TemplateExport;
use App\Exports\Clients\ClientExport;
use App\Exports\Clients\QrcodeExport;
use App\Exports\ClientsTicketsExport;
use App\Http\Controllers\Controller;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;
use App\Http\Requests\Admin\Clients\CreateRequest;
use App\Http\Requests\Admin\Clients\GenerateClientRequest;
use App\Http\Requests\Admin\Clients\ListRequest;
use App\Http\Requests\Admin\Clients\SavePrintRequest;
use App\Http\Requests\Admin\Clients\UpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\Common\UploadFileRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Admin\ClientService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Services\Videc\TicketAnalyticsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ClientController extends Controller
{
    private readonly TicketAnalyticsService $ticketAnalyticsService;

    public function __construct(ClientService $service, TicketAnalyticsService $ticketAnalyticsService)
    {
        $this->service = $service;
        $this->ticketAnalyticsService = $ticketAnalyticsService;
    }

    /**
     * Show the application clients index.
     */
    public function index(Event $event, ListRequest $request)
    {
        $this->authorize('list_client', $event);

        $dataTable = new ClientDataTable($event);

        if (session()->has("import_clients_errors_{$event->id}")) {
            $this->cancelImport("import_clients_errors_{$event->id}");
        }

        $total = $dataTable->getFilter();
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($event->code);

        /* label */
        $labels = $event->labels;
        $label = $labels->first();

        if (!empty($label)) {
            $clients = $this->service->getListByAttributes([
                'event_id'  => $label->event->id,
                "type"      => $label->type
            ]);
        }
        $cfFilters = $this->service->getFilterCustomFields($event->id);

        return $dataTable->render('admin.clients.index', [
            'event'                 => $event,
            'total'                 => $total->count(),
            'notHavingImgQrcodes'   => $total->where('img_qrcode', '=', null)->count(),
            'totalCheckedIn'        => $totalCheckedIn->count(),
            'label'                 => $label ?? null,
            'labelDetail'           => $this->service->label_detail()->init(),
            'labelDetails'          => $label->label_details ?? null,
            'clients'               => $clients ?? null,
            'cfFilters'             => $cfFilters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event): View
    {
        $this->authorize('create_client', $event);

        /* button print */
        $labels = $event->labels;
        $label = $labels->first();

        // if ($label && in_array($label->status, [
        //     Label::STATUS_NEW,
        //     Label::STATUS_ACTIVE
        // ])) {

        // }

        return view('admin.clients.detail', [
            'event'                 => $event,
            'model'                 => $this->service->init(),
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $event->getCustomFieldTemplates(),
            /* label */
            'labels'                => $labels ?? null,
            'label'                 => $label ?? null,
        ]);
    }

     /**
     * Display the specified resource edit form.
     */
    public function edit(Client $client, Request $request)
    {
        $this->authorize('edit', $client);

        $dataTable = new CheckinDataTable($client->event, $client->qrcode);
        $totalCheckedIn = $dataTable->getFilter();
        $labels = $client->event->labels;
        $label = $labels->first();
        $ticketHistory = $this->ticketAnalyticsService->clientHistory($client);

        /* campaign */
        $campaigns = $this->service->campaign()->getQueryByAttributes([
            'event_id' => $client->event_id
        ])
            ->select(['id', 'name'])
            ->get();

        $cards = $this->service->card()->getQueryByAttributes([
            'event_id' => $client->event_id
        ])
            ->select(['id', 'code'])
            ->get();

        return $dataTable->render('admin.clients.detail', [
            'event'                 => $client->event,
            'model'                 => $client,
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $client->event->getCustomFieldTemplates(),
            'totalCheckedIn'        => $totalCheckedIn->count(),
            'labels'                => $labels,
            'label'                 => $label,
            /* campaigns */
            'campaigns'             => $campaigns ?? null,
            'cards'                 => $cards ?? null,
            'ticketHistory'         => $ticketHistory,
        ]);
    }

    public function savePrint(SavePrintRequest $request)
    {
        $attributes = $request->only(['event_id', 'event_code', 'qrcode', 'name', 'email', 'status', 'type', 'custom_fields', 'avatar']);
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['regiser_source'] = Client::REGISTER_ADMIN;

        // if ()
        $client = $this->service->create($attributes);

        /* checkin */
        // $checkin = new MiddlewareCheckinService(
        //     $attributes['event_code'],
        //     $attributes['qrcode'],
        //    now()->format('Y-m-d H:i:s'),
        // );
        // $checkin->attributes = [
        //     'custom_fields' => $attributes['custom_fields'] ?? [],
        //     'user_group'    => EventSetting::GROUP_DESKTOP,
        // ];
        // $checkin->checkin();
        /* end */

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        sleep(1.5); // wait for img_qrcode generated

        $avatar = $request->file('avatar');

        if ($avatar) {
            $this->service->attributes['image'] = $avatar;
            $this->service->attributes['name'] = $avatar->getClientOriginalName();

            if ($result = $this->service->mediaLibraryService()->store()) {
                if (!empty($result['media'])) {
                    $this->service->update($client->id, [
                        'avatar' => $result['media']->id
                    ]);

                    if ($client->avatar) {
                        $this->service->mediaLibraryService()->deleteMedia($client->avatar);
                    }
                } else {
                    // return redirect()->route('admin.clients.edit', [
                    //     'client'    => $client,
                    // ])->withErrors($result['msg']);
                }
            }
        }

        $event = $client->event;
        $labels = $event->labels;
        $label = $labels->first();
        $labelDetails = $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null;

        return $this->responseSuccess([
            'html' => view('components.label_details.to-print', [
                'label'         => $label,
                'labelDetails'  => $labelDetails,
                'event'         => $event,
                'client'        => $client,
                'display'       => true,
            ])
                ->render(),
            'redirectTo' => route('admin.clients.edit', [
                'client'    => $client,
            ])
        ], "Đăng ký thành công");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        $attributes = $request->only(['event_id', 'event_code', 'qrcode', 'name', 'email', 'status', 'type', 'custom_fields', 'avatar']);
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['regiser_source'] = Client::REGISTER_ADMIN;
        $client = $this->service->create($attributes);

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        return redirect()->route('admin.clients.edit', [
            'client'    => $client,
        ])->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Client $client): RedirectResponse
    {
        $attributes = $request->only(['name', 'email', 'status', 'type', 'custom_fields', 'avatar']);
        $attributes['updated_by'] =  auth()->id();
        $attributes['regiser_source'] = Client::REGISTER_ADMIN;
        $this->service->update($client->id, $attributes);

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        $avatar = $request->file('avatar');

        if ($avatar) {
            $this->service->attributes['image'] = $avatar;
            $this->service->attributes['name'] = $avatar->getClientOriginalName();

            if ($result = $this->service->mediaLibraryService()->store()) {
                if (!empty($result['media'])) {
                    $this->service->update($client->id, [
                        'avatar' => $result['media']->id
                    ]);

                    if ($client->avatar) {
                        $this->service->mediaLibraryService()->deleteMedia($client->avatar);
                    }
                } else {
                    return redirect()->route('admin.clients.edit', [
                        'client'    => $client,
                    ])->withErrors($result['msg']);
                }
            }
        }

        return redirect()->route('admin.clients.edit', [
            'client'    => $client,
        ])->withSuccess("Cập nhật thành công");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        $this->service->destroy($client);

        return redirect()->route('admin.clients.index', [
            'event' => $client->event
        ])->withSuccess("Đã xoá khách mời");
    }

    public function import(Event $event)
    {
        $this->authorize('import_client', $event);

        $dataTable = new ClientDataTable($event);
        $total = $dataTable->getFilter();
        $file = $this->service->imp_exp_file()->findByAttributes([
            'event_id'  => $event->id,
        ], [], [], [
            'id'        => 'DESC'
        ]);

        return $dataTable->render('admin.clients.import', [
            'model' => $this->service->init(),
            'event' => $event,
            'file'  => $file ?? null,
            'total' => $total->count(),
        ]);
    }

    public function upload(Event $event, UploadFileRequest $request)
    {
        $this->service->setAttributes($request->all());

        if (session()->has("import_clients_errors_{$event->id}")) {
            $this->cancelImport("import_clients_errors_{$event->id}");
        }

        if ($result = $this->service->upload($event)) {
            if ($result['success']) {
                return redirect()->route('admin.clients.import', [
                    'event' => $event,
                    'file'  => $result['file'],
                ])->withSuccess($result['msg']);
                // return redirect()->route('admin.clients.index', $event)->withSuccess($result['msg']);
            }
        }

        return redirect()->route('admin.clients.import', $event)->withErrors($result['msg'] ?? "Đã có lỗi xảy ra, chưa hoàn tất nạp file");
    }

    public function exportTemplateImport(Event $event)
    {
        $this->authorize('export_template_import_client', $event);

        return Excel::download(new TemplateExport($event), "{$event->code}_template_khach_hang.xlsx");
    }

    /**
     * Export file excel
     */
    public function exportList(Event $event, Request $request)
    {
        $this->authorize('export_list_client', $event);

        $file = "public/exports/{$event->code}_ds_khach_hang_".date('Ymd_His').'.xlsx';
        $filePath = storage_path("app/{$file}");

        Excel::store(
            new ClientExport($event),
            $file
        );

        return response()->download($filePath)->deleteFileAfterSend(true);

        /* $filters = [];

        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }

        if ($request->filled('type')) {
            $filters['type'] = $request->input('type');
        }

        if ($request->filled('register_source')) {
            $filters['register_source'] = $request->input('register_source');
        }

        if ($request->filled('field_date') && $request->filled('from_date') && $request->filled('to_date')) {
            $filters['field_date'] = $request->input('field_date');
            $filters['from_date'] = $request->input('from_date');
            $filters['to_date'] = $request->input('to_date');
        } */

        return Excel::download(
            new ClientExport($event),
            "{$event->code}_ds_khach_hang_".date('Ymd_His').'.xlsx'
        );
    }

    public function exportQrcodes(Event $event)
    {
        $this->authorize('export_list_client', $event);

        $file = "public/exports/{$event->code}_ds_qrcodes_".date('Ymd_His').'.xlsx';
        $filePath = storage_path("app/{$file}");

        Excel::store(
            new QrcodeExport($event),
            $file
        );

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function fillQrcode(Event $event, Request $request)
    {
        $this->authorize('fill_qrcode', $event);

        $attributes = $request->only(['phone', 'email', 'name', 'custom_fields']);
        $qrcode = $event->generateQrcodeOnSetting(
            $event->code,
            $attributes['custom_fields']['phone'] ?? null,
            $attributes['email'] ?? null,
            $attributes['name'] ?? null,
            $attributes['custom_fields'] ?? [],
        );
        return $this->responseSuccess([
            'qrcode' => $qrcode,
        ], "");
    }

    public function getTemplateQrcodes(Event $event, Request $request)
    {
        $this->authorize('get_template_qrcodes', $event);

        $request->validate([
            'count'          => 'required|numeric|min:1|max:5000',
            'type'           => 'nullable|string',
        ]);

        $count = $request->input('count', 100);
        $type = $request->type;
        return Excel::download(new TemplateExport($event, $count, $type), "{$event->code}_{$count}_qrcodes_template_khach_hang.xlsx");
    }

    public function generate(Event $event, GenerateClientRequest $request)
    {
        $count = $request->input('count', 100);
        $type = $request->type;

        if ($result = $this->service->generateClients($event, $count, $type)) {
            if (is_array($result)) {
                if ($result['success']) {
                    return $this->responseSuccess([
                        'redirectTo' => route('admin.clients.index', $event)
                    ], $result['msg']);
                }

                return $this->responseError($result['msg']);
            }
        }

        return $this->responseError("Có lỗi xảy ra trong quá trình tạo mới {$count} khách hàng");
    }

    /**
     * Remove all clients based on current filters
     */
    public function destroyAll(Event $event, ListRequest $request)
    {
        $request->validate([
            'confirm'          => 'required|string',
        ]);

        if ($request->input('confirm') != 'DELETE') {
            return redirect()->route('admin.clients.index', [
                'event' => $event
            ])->withErrors("Khổng thể reset danh sách khách mời");
        }

        $query = $this->service->getQuery()
            ->where('event_id', $event->id);
            // ->where('status', '!=', Client::STATUS_DELETED);

        $query = $this->service->applyFilters($query);
        $count = $query->count();
        $clients = $query->get();
        $batchKey = Str::random(11);
        $resultResetCheckin = $this->service->checkin()->destroyAll($event);

        if ($resultResetCheckin['status']) {
            foreach ($clients as $client) {
                $this->service->destroy($client, $batchKey);
            }
        } else {
            return redirect()->route('admin.clients.index', [
                'event' => $event
            ])->withErrors("Quá trình backup dữ liệu checkin không thành công");
        }

        return redirect()->route('admin.clients.index', [
            'event' => $event
        ])->withSuccess("Đã xóa {$count} khách mời");
    }

    public function downloadQrcodeImages(Event $event, ListRequest $request)
    {
        $this->authorize('download_qrcode_images', $event);

        $query = $this->service->getQuery()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED);

        $query = $this->service->applyFilters($query);
        $count = $query->count();
        $clients = $query->get();
        $zipFileName = "{$event->code}_{$count}_qrcodes_".now()->timestamp.".zip";
        // $folderName = strtolower($event->code);
        // $zipPath = storage_path("app/public/qrcodes/{$folderName}/{$zipFileName}");
        $zipPath = storage_path("app/public/qrcodes/{$zipFileName}");

        if (!Storage::disk('public')->exists('tmp')) {
            Storage::disk('public')->makeDirectory('tmp');
        }

        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return back()->withErrors([
                    'Không thể tạo file ZIP.'
                ]);
            }

            foreach ($clients as $client) {
                if ($client->img_qrcode) {
                    $path = "public/{$client->img_qrcode}";

                    if (Storage::exists($path)) {
                        $zip->addFile(storage_path("app/{$path}"), basename($path));
                    } else {
                        Artisan::call("generate:image-qrcode {$client->event_code} --qrcode={$client->qrcode}");
                        $zip->addFile(storage_path("app/{$path}"), basename($path));
                    }
                }
            }

            $zip->close();
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            Log::error($th);

            if (auth()->user()->isSysAdmin()) {
                return back()->withErrors("Đã có lỗi xảy ra trong quá trình tạo và tải qrcodes: {$th->getMessage()}");
            }
        }

        return back()->withErrors("Đã có lỗi xảy ra trong quá trình tạo và tải qrcodes");
    }

    public function generateQrcodeImages(Event $event, ListRequest $request)
    {
        /* $query = $this->service->getQuery()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED);

        $query = $this->service->applyFilters($query);
        $count = $query->count();
        $clients = $query->get(); */

        $type = $request->type;
        $this->service->generateQrcodeImages($event->code, null, $type);
        return back()->withSuccess("Qrcodes đang được tạo");
    }

    public function getData(Event $event, Request $request)
    {
        $this->authorize('get_data_client', $event);

        /* include deleted */
        $clients = $this->service->getListByAttributes([
            'event_id'  => $event->id,
        ], [], array_filter([
            'qrcode'    => $request->qrcode,
            'name'      => $request->name,
            'email'     => $request->email,
        ]), 50, [], false);
        $showTicketSummary = $event->code === 'videc-2026';
        $customFieldTemplates = $event->getCustomFieldTemplates();
        $ticketHistories = $showTicketSummary
            ? $this->ticketAnalyticsService->clientSummaries($this->clientCollection($clients))
            : [];

        return $this->responseSuccess([
            'html'          => view('admin.reports.clients._tbody', [
                'event'                 => $event,
                'clients'               => $clients,
                'customFieldTemplates'  => $customFieldTemplates,
                'countCol'              => 10 + count($customFieldTemplates) + ($showTicketSummary ? 1 : 0),
                'ticketHistories'       => $ticketHistories,
                'showTicketSummary'     => $showTicketSummary,
            ],
            )->render(),
            'pagination'    => $clients->links(),
        ], null);

        return view('admin.reports.clients._tbody', compact('event', 'clients'))->render();

        $dataTable = new ClientDataTable($event);
        return $dataTable->ajax();
    }

    public function sendClientEmail(Client $client, Request $request)
    {
        if ($this->service->sendClientEmail($client, $request->campaign_id)) {
            return redirect()->route('admin.clients.edit', [
                'client'    => $client,
            ])->withSuccess("Gửi mail thành công");
        }

        return back()->withErrors("Không thể gửi mail");
    }

    /* customize */
    /* hidec-vn */
    public function exportHidecVn(Event $event)
    {
        // If you want the last column to be ticket index instead of constant 2, pass null:
        // new ClientsTicketsExport(lastColumnValue: null)

        return Excel::download(
            new ClientsTicketsExport($event, lastColumnValue: 2), // matches your example
            'ds_luckydraw.xlsx'
        );
    }

    public function generateClientCard(Request $request, string $clientId)
    {
        $cardId = intval($request->card_id);
        $clientId = intval($request->client_id);

        $card = $this->service->card()->findByAttributes([
            'id' => $cardId
        ]);

        if (!$card) {
            return back()->withErrors("Card not found.");
        }

        $client = $this->service->findByAttributes([
            'id' => $clientId
        ]);

        if (!$client) {
            return back()->withErrors("Client not found.");
        }

        $result = $this->service->middleware_card()->generateCardNow($cardId, $clientId);

        if ($result['status']) {
            $client->refresh();
            if (!empty($client->document_pdf) && Storage::exists("public/{$client->document_pdf}")) {
                return back()->withSuccess("Thiệp đã được tạo");
            } else {
                return back()->withErrors("Không tìm thấy file. Vui lòng thử lại sau...");
            }
        } else {
            return back()->withErrors($result['msg']);
        }

        return back()->withErrors("Error");
    }

    private function clientCollection($clients)
    {
        if (method_exists($clients, 'getCollection')) {
            return $clients->getCollection();
        }

        return collect($clients);
    }
}
