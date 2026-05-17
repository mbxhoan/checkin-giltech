<?php

namespace App\Http\Controllers\Admin;

use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\Admin\CheckinDataTable;
use App\Exports\Checkins\CheckInCountExport;
use App\Exports\Checkins\CheckInOutExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Checkins\CheckinRequest;
use App\Http\Requests\Admin\Checkins\ListRequest;
use App\Http\Requests\Admin\Checkins\ViewConfigRequest;
use App\Services\Admin\CheckinService;
use Illuminate\Http\RedirectResponse;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSetting;
use Illuminate\Http\Request;
use App\Models\Client;

class CheckinController extends Controller
{
    public function __construct(CheckinService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application products index.
     */
    public function index(Event $event)
    {
        $this->authorize('list_checkin', $event);

        $dataTable = new CheckinDataTable($event);
        $total = $dataTable->getFilter();
        $totalCheckedIn = $this->service->middleware_client()->countClientByCheck($event->code, 'CHECKIN');
        $totalCheckedOut = $this->service->middleware_client()->countClientByCheck($event->code, 'CHECKOUT');
        $clients = $this->service->client()->getListByAttributes([
            'event_id' => $event->id
        ]);

        return $dataTable->render('admin.checkins.index', [
            'event'             => $event,
            'model'             => Checkin::getModel(),
            'total'             => $total->count(),
            'clients'           => $clients,
            'totalCheckedIn'    => $totalCheckedIn,
            'totalCheckedOut'   => $totalCheckedOut,
            'scan_types'        => Client::getAvailableTypes($event->id),
        ]);
    }

    public function config(Event $event, ViewConfigRequest $request)
    {
        $this->authorize('config_checkin', $event);

        return view('admin.checkins.config', [
            'defaultScreen'         => $request->screen ?? "desktop",
            'defaultMsg'            => $request->msg ?? "success",
            'mainBg'                => empty($request->screen) || $request->screen == "desktop" ?
                ($event->main_bg_desktop ? $event->mainBgDesktop->getUrl() : null) :
                ($event->main_bg_mobile ? $event->mainBgMobile->getUrl() : null),
            'event'                 => $event,
            'clients'               => $this->service->client()->getListByAttributes(),
            'settings'              => $this->service->event_setting()->getListByAttributes([
                'event_id'          => $event->id,
                'group'             => strtoupper($request->screen ?? "desktop"),
                'status'            => [
                    EventSetting::STATUS_ACTIVE
                ]
            ]),
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                'event_id'          => $event->id,
            ], [], [], 0, [
                'order'             => 'ASC',
            ]),
            'screens'               => [
                'desktop'           => 'Desktop',
                'mobile'            => 'Mobile',
            ],
            'messages'              => [
                'success'           => [
                    "text"          => "Thành công",
                    "msg"           => __('responses.checkin.success'), // hoặc số lần checkin
                    "showInfo"      => true,
                ],
                'failed'            => [
                    "text"          => "Thất bại",
                    "msg"           => __('responses.checkin.errors.no_data_found'),
                    "showInfo"      => false,
                ],
                'duplicated'        => [
                    "text"          => "Trùng",
                    "msg"           => __('responses.checkin.errors.duplicate_checkin'),
                    "showInfo"      => true,
                ],
            ],
            'audio'                 => $this->service->audio()->init(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkin(CheckinRequest $request): RedirectResponse
    {
        $this->service->attributes = $request->only(['event_code', 'qrcode', 'type']);

        if ($result = $this->service->checkin()) {
            if (is_array($result)) {
                if ($result['checkin']) {
                    $model = $result['model'];
                    if ($model) {
                        /* customize */
                        /* abinbev */
                        if (isset($result['checkin']) && $result['checkin']) {
                            $client = $result['client'] ?? null;
                            if (!empty($client) && in_array($client->event_code, [
                                'abinbev',
                            ])) {
                                if (isset($client->custom_fields['gotit']) && $client->custom_fields['gotit']) {
                                    // Gửi email voucher khi khách có thông gotit và checkin thành công
                                    $this->service->middleware_email()->sendEmailGlobalByClient($client, 279);
                                }
                            }
                        }
                        // toastr()->success($result['msg']);
                        return back()->withSuccess($result['msg']);
                    }
                }

                return back()->withErrors($result['msg']);
            }
        }

        return back()->withErrors(__('responses.error'));
    }

    public function renderBackground(Event $event, string $screen, string $msg)
    {
        $this->authorize('render_background_checkin', $event);

        return $this->responseSuccess([
            'html' => view('admin.checkins._background', [
                'event'                 => $event,
                'screen'                => $screen,
                'mainBg'                => $screen == "desktop" ?
                    ($event->main_bg_desktop ? $event->mainBgDesktop->getUrl() : null) :
                    ($event->main_bg_mobile ? $event->mainBgMobile->getUrl() : null),
                'cfTemplate'            => $this->service->custom_field_template()->init(),
                'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                    'event_id'          => $event->id,
                ], [], [], 0, [
                    'order'             => 'ASC',
                ]),
                'msg'                   => $msg,
                'messages'              => [
                    'success'           => [
                        "text"          => "Thành công",
                        "msg"           => __('responses.checkin.success'), // hoặc số lần checkin
                        "showInfo"      => true,
                    ],
                    'failed'            => [
                        "text"          => "Thất bại",
                        "msg"           => __('responses.checkin.errors.no_data_found'),
                        "showInfo"      => false,
                    ],
                    'duplicated'        => [
                        "text"          => "Trùng",
                        "msg"           => __('responses.checkin.errors.duplicate_checkin'),
                        "showInfo"      => true,
                    ],
                ],
                'customCheckinMessages' => $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [],
            ])->render()
        ]);
    }

    public function exportCheckInOutReport(Event $event, ?string $qrcode = null)
    {
        $this->authorize('export_report_checkin', $event);

        $fileName = "{$event->code}_bao_cao_check_in_out.xlsx";

        if ($qrcode) {
            $fileName = "{$event->code}_{$qrcode}_bao_cao_check_in_out.xlsx";
        }

        return Excel::download(new CheckInOutExport($event, $qrcode), $fileName);
    }

    public function exportCheckInCount(Event $event, ?string $qrcode = null)
    {
        $this->authorize('export_report_checkin', $event);

        $fileName = "{$event->code}_bao_cao_so_lan_check_in.xlsx";

        if ($qrcode) {
            $fileName = "{$event->code}_{$qrcode}_bao_cao_so_lan_check_in.xlsx";
        }

        return Excel::download(new CheckInCountExport($event, $qrcode), $fileName);
    }

    /**
     * Remove all clients based on current filters
     */
    public function destroyAll(Event $event, ListRequest $request)
    {
        $request->validate([
            'confirm'          => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        if ($request->input('confirm') != 'DELETE') {
            return redirect()->route('admin.checkins.index', [
                'event' => $event
            ])->withErrors("Khổng thể reset danh sách khách mời");
        }

        $result = $this->service->destroyAll($event);
        return redirect()->route('admin.checkins.index', [
            'event' => $event
        ])->withSuccess("Đã xóa {$result['count']} dữ liệu checkin");
    }

    public function destroyByQrcode(Event $event, string $qrcode, Request $request)
    {
        $request->validate([
            'confirm'          => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        $result = $this->service->destroyAll($event, $qrcode);

        if ($request->_redirect) {
            return redirect($request->_redirect)->withSuccess("Đã xóa {$result['count']} dữ liệu checkin");
        }

        return redirect()->route('admin.checkins.index', [
            'event' => $event
        ])->withSuccess("Đã xóa {$result['count']} dữ liệu checkin");
    }

    public function destroyByClient(int $clientId, Request $request)
    {
        $request->validate([
            'confirm'          => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        $client = $this->service->client()->findByAttributes([
            'id' => $clientId,
        ]);

        if (empty($client)) {
            return back()->withErrors("Khổng thể reset khách mời");
        }

        $event = $client->event;
        $qrcode = $client->qrcode;
        $result = $this->service->destroyAll($event, $qrcode);

        if ($request->_redirect) {
            return redirect($request->_redirect)->withSuccess("Đã xóa {$result['count']} dữ liệu checkin");
        }

        return redirect()->route('admin.checkins.index', [
            'event' => $event
        ])->withSuccess("Đã xóa {$result['count']} dữ liệu checkin");
    }
}
