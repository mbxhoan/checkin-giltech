<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scan\CheckinRequest;
use App\Http\Requests\Scan\MultiCheckinRequest;
use App\Models\Client;
use App\Services\Scan\ScanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ScanController extends Controller
{
    public function __construct(ScanService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        $user = Auth::user();

        return view('scan.index', [
            'events' => $user->event_id
                ? $user->company->events->where('id', $user->event_id)->values()
                : $user->company->events,
        ]);
    }

    public function scan(Event $event)
    {
        $agent = new Agent();

        if (auth()->user()->event_id) {
            if ($event->id != auth()->user()->event_id) {
                abort(404);
            }
        }

        $mainBg = $event->main_bg_mobile ? $event->mainBgMobile->getUrl() : null;
        $screen = "mobile";
        $col = 'is_checkin_mobile';
        $userGroup = EventSetting::GROUP_MOBILE;
        $labels = $event->labels;
        $label = $labels->first();

        if ($agent->isDesktop()) {
            $mainBg = $event->main_bg_desktop ? $event->mainBgDesktop->getUrl() : null;
            $screen = "desktop";
            $col = 'is_checkin_desktop';
            $userGroup = EventSetting::GROUP_DESKTOP;
        }

        $eventSettings = $event->getEventSettings($userGroup)
            ->pluck('value', 'name')
            ->toArray();

        /* sync settings from redis */
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['user_group'] = $userGroup;
        // $this->service->getEventSettings();

        return view('scan.scan', [
            'event'                 => $event,
            'screen'                => $screen,
            'mainBg'                => $mainBg,
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                'event_id'          => $event->id,
                $col                => true,
            ], [], [], 0, [
                'order'             => 'ASC',
            ]),
            // 'eventSettings'         => $eventSettings,
            'customCheckinMessages' => $event->custom_checkin_messages ? (json_decode($event->custom_checkin_messages, true)[$screen] ?? []) : [],
            'label'                 => isset($eventSettings['ALLOW_CHECKIN_PRINT']) && $eventSettings['ALLOW_CHECKIN_PRINT'] ? ($label ?? null) : null,
            'agent'                 => $agent,
            'clients'               => $this->service->client()->getListByAttributes([
                'event_id'          => $event->id,
            ]),
        ]);
    }

    public function checkin(CheckinRequest $request)
    {
        $agent = new Agent();
        $this->service->attributes = $request->all();
        unset(
            $this->service->attributes['type'],
            $this->service->attributes['checkout_action'],
        );
        $this->service->attributes['user_group'] = EventSetting::GROUP_MOBILE;
        $this->service->attributes['by_pass_duplicate'] = $request->has('by_pass_duplicate') && $request->input('by_pass_duplicate') ? (bool)$request->by_pass_duplicate : false;

        if ($agent->isDesktop()) {
            $this->service->attributes['user_group'] = EventSetting::GROUP_DESKTOP;
        }

        $authUser = auth()->user();
        $isCheckoutScanner = !empty($authUser) && $authUser->is_checkout;
        $checkoutAction = $this->normalizeCheckoutAction($request->input('checkout_action'));

        if ($isCheckoutScanner) {
            $eventCode = (string) $request->input('event_code');
            $qrcode = (string) $request->input('qrcode');
            $hasCheckinRecord = $this->hasCheckinRecord($eventCode, $qrcode);

            if (!$hasCheckinRecord && empty($checkoutAction)) {
                $client = $this->findClient($eventCode, $qrcode);

                return $this->responseSuccess([
                    'checkin'                   => false,
                    'requires_checkout_confirm' => true,
                    'fields'                    => $this->getClientFields($client),
                ], 'Mã này chưa checkin. Vui lòng chọn thao tác checkout.');
            }

            if (!$hasCheckinRecord && $checkoutAction === 'checkin_and_checkout') {
                $scanTime = now()->format('Y-m-d H:i:s');
                $this->service->attributes['scan_time'] = $scanTime;
                $this->service->attributes['type'] = Checkin::TYPE_CHECKIN;

                $checkinResult = $this->service->checkin();
                if (!is_array($checkinResult) || !($checkinResult['checkin'] ?? false)) {
                    return $this->responseError(is_array($checkinResult) ? ($checkinResult['msg'] ?? __('responses.error')) : __('responses.error'), 400);
                }
            }

            $this->service->attributes['type'] = Checkin::TYPE_CHECKOUT;
        }

        if ($result = $this->service->checkin()) {
            if (is_array($result)) {
                return $this->buildCheckinResponse($result);
            }
        }

        return $this->responseError(__('responses.error'), 400);
    }

    private function buildCheckinResponse(array $result): JsonResponse
    {
        $client = $result['client'] ?? null;

        /* customize */
        /* abinbev */
        if (($result['checkin'] ?? false) && !empty($client)) {
            if (in_array($client->event_code, [
                'abinbev',
            ])) {
                if (isset($client->custom_fields['gotit']) && $client->custom_fields['gotit']) {
                    // Gửi email voucher khi khách có thông gotit và checkin thành công
                    $this->service->middleware_email()->sendEmailGlobalByClient($client, 279);
                }
            }
        }

        /* customize HNT4*/
        $shouldPrint = false;
        if (($result['checkin'] ?? false) && !empty($client) && $client->event_code === 'hnt4') {
            $allTicketTypes    = ['tien_hoi_nghi', 'ngay_1', 'ngay_2'];
            $clientTickets     = $client->custom_fields['tickets'] ?? [];
            $hasAllTickets     = count(array_intersect($allTicketTypes, $clientTickets)) === count($allTicketTypes);

            if ($hasAllTickets) {
                // Client có đủ 3 loại vé → chỉ in 1 lần duy nhất cho toàn sự kiện (lần checkin đầu tiên)
                $totalCheckin = Checkin::where('event_code', $client->event_code)
                    ->where('qrcode', $client->qrcode)
                    ->where('status', '!=', Checkin::STATUS_DELETED)
                    ->count();
                $shouldPrint = $totalCheckin === 1;
            } else {
                // Client không đủ 3 loại vé → in lần đầu tiên mỗi ngày
                $today = Carbon::now()->toDateString();
                $todayCheckin = Checkin::where('event_code', $client->event_code)
                    ->where('qrcode', $client->qrcode)
                    ->where('status', '!=', Checkin::STATUS_DELETED)
                    ->whereDate('scan_time', $today)
                    ->count();
                $shouldPrint = $todayCheckin === 1;
            }
        }

        return $this->responseSuccess([
            'checkin'       => $result['checkin'] ?? false,
            'model'         => $result['model'] ?? null,

            /* customize */
            /* hidec-2025 */
            'fields'        => $this->getClientFields($client),
            'is_duplicated' => $result['is_duplicated'] ?? false,
            'count'         => $result['count'] ?? null,
            'should_print'  => $shouldPrint,
        ], $result['msg']);
    }

    private function findClient(string $eventCode, string $qrcode): ?Client
    {
        return Client::where([
            'event_code'    => $eventCode,
            'qrcode'        => $qrcode,
        ])
            ->whereIn('status', [
                Client::STATUS_ACTIVE,
                Client::STATUS_NEW,
            ])
            ->first();
    }

    private function hasCheckinRecord(string $eventCode, string $qrcode): bool
    {
        return Checkin::where([
            'event_code'    => $eventCode,
            'qrcode'        => $qrcode,
            'type'          => Checkin::TYPE_CHECKIN,
        ])
            ->where('status', '!=', Checkin::STATUS_DELETED)
            ->exists();
    }

    private function getClientFields(?Client $client): array
    {
        if (empty($client)) {
            return [];
        }

        return array_merge($client->getFullFields(in_array($client->event_code, [
            'hidec-2025',
        ]) ? false : false), ['id' => $client->id]);
    }

    private function normalizeCheckoutAction(?string $checkoutAction): ?string
    {
        return in_array($checkoutAction, [
            'checkout_only',
            'checkin_and_checkout',
        ], true) ? $checkoutAction : null;
    }

    public function renderLabel(Label $label, Request $request)
    {
        $client = $request->client_id ? $this->service->client()->findByAttributes([
            'id'                => $request->client_id
        ]) : null;

        return $this->responseSuccess([
            'html'                  => view('components.label_details.to-print', [
                'label'             => $label,
                'labelDetails'      => $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null,
                'event'             => $label,
                'display'           => true,
                'client'            => $client,
            ])->render(),
            'client'                => $client,
        ]);
    }

    public function syncOffline(MultiCheckinRequest $request)
    {
        $agent = new Agent();
        $this->service->attributes = $request->all();
        $this->service->attributes['user_group'] = EventSetting::GROUP_MOBILE;

        if ($agent->isDesktop()) {
            $this->service->attributes['user_group'] = EventSetting::GROUP_DESKTOP;
        }

        if ($this->service->multiCheckin()) {
            return $this->responseSuccess([
                'synced_at'     => date('Y-m-d H:i:s'),
            ], 'Đã đồng bộ danh sách checkin thành công');
        } else {
            return $this->responseError('Không tìm thấy thông tin', 400);
        }
    }

    public function updateField(Request $request)
    {
        $qrcode = $request->qrcode;
        $eventCode = $request->event_code;

        $client = Client::where([
            'qrcode'        => $qrcode,
            'event_code'    => $eventCode,
        ])->first();

        if ($client) {
            $orgCustomFields = $client->custom_fields;
            $newCustomFields = $orgCustomFields;
            $updateFields = $request->only([
                'custom_fields',
            ])['custom_fields'];

            foreach ($updateFields as $fieldName => $fieldValue) {
                $newCustomFields[$fieldName] = $fieldValue;
                /* customize */
                /* next-level */
                if ($fieldName == "allow_image_use") {
                    $newCustomFields["confirmation_time"] = now()->format('Y-m-d H:i:s');
                }
            }

            $client->update([
                'custom_fields' => $newCustomFields
            ]);

            return $this->responseSuccess(null, 'Cập nhật thành công');
        }

        return $this->responseError('Không tìm thấy thông tin', 400);
    }
}
