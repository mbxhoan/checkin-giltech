<?php
namespace App\Services\Middleware;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventSetting;
use App\Services\BaseService;

class CheckinService extends BaseService
{
    public $event;
    public $eventCode;
    public $qrcode;
    public $scanTime;
    public $byPassDuplicate = false;

    public function __construct(string $eventCode, ?string $qrcode = null, ?string $scanTime = null, bool $byPassDuplicate = false)
    {
        $this->model = resolve(Checkin::class);
        $this->eventCode = $eventCode;
        $this->qrcode = $qrcode;
        $this->scanTime = $scanTime ?? now()->format('Y-m-d H:i:s');
        $this->byPassDuplicate = $byPassDuplicate;
    }

    public function event()
    {
        return app(EventService::class);
    }
    public function client()
    {
        return new ClientService();
    }

    public function checkin()
    {
        $this->attributes['type'] = !empty($this->attributes['type']) ? $this->attributes['type'] : null;
        $this->attributes['custom_fields'] = !empty($this->attributes['custom_fields']) ? $this->attributes['custom_fields'] : [];
        return $this->check();
    }

    public function multiCheckin()
    {
        $this->attributes['type'] = !empty($this->attributes['type']) ? $this->attributes['type'] : null;
        $totalRecords = $this->attributes['total_records'];
        $datas = $this->attributes['data'];

        if (count($datas) > 0) {
            $record = 0;

            foreach ($datas as $index => $data) {
                $this->qrcode = $data['qrcode'];
                $this->scanTime = $data['scan_time'] ?? now()->format('Y-m-d H:i:s');
                $this->attributes['custom_fields'] = !empty($data['custom_fields']) ? $data['custom_fields'] : [];

                if ($result = $this->check()) {
                    if (is_array($result)) {
                        if ($result['checkin']) {
                            if ($result['model']) {
                                $record++;
                                continue;
                            }
                        }

                        $errors[++$index] = [
                            'qrcode'    => $this->qrcode,
                            'error'     => $result['msg'],
                        ];
                    }
                }
            }

            return [
                'checkin'       => true,
                'msg'           => [
                    'total'     => "{$totalRecords} record(s)",
                    'checked'   => $record,
                    'failed'    => count($errors ?? [])." record(s)",
                    'errors'    => $errors ?? [],
                ],
            ];
        }

        return [
            'checkin'       => false,
            'msg'           => __('responses.checkin.errors.no_data_found'),
        ];;
    }

    public function getCustomMessages()
    {
        $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $customCheckinMessages = $this->getRedis("checkin_custom_messages", $this->event->code, "array");

        if (!count($customCheckinMessages)) {
            $customCheckinMessages = $this->event->custom_checkin_messages ? json_decode($this->event->custom_checkin_messages, true) : [];
            $this->updateRedis("checkin_custom_messages", $this->event->code, json_encode($customCheckinMessages), config("app.times.seconds.five-minutes"));
            $customCheckinMessages = $this->getRedis("checkin_custom_messages", $this->event->code, "array");
        }

        $customCheckinMessages = count($customCheckinMessages) && isset($customCheckinMessages[strtolower($userGroup)]) ? $customCheckinMessages[strtolower($userGroup)] : [];
        return $customCheckinMessages;
    }

    public function getEventSettings()
    {
        $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $eventSettings = $this->getRedis("event_settings:{$userGroup}", $this->event->code, "array");

        if (empty($eventSettings) || !count($eventSettings)) {
            $eventSettings = $this->event->getEventSettings($userGroup);
            $this->updateRedis("event_settings:{$userGroup}", $this->event->code, json_encode($eventSettings), config("app.times.seconds.five-minutes"));
            $eventSettings = $this->getRedis("event_settings:{$userGroup}", $this->event->code, "array");
        }

        $checkinSettings = $this->getRedis("checkin_settings:{$userGroup}", $this->event->code, "array");
        if (empty($checkinSettings) || !count($checkinSettings)) {
            $checkinSettings = $this->event->getEventSettings($userGroup);
            $settingKeys = [
                'show_checkin_count',
                'allow_checkin_nodata',
                'allow_checkin_by_date',
                'allow_checkin_by_user',
                'no_duplicate_checkin',
            ];

            $eventSettings = array_filter($eventSettings, function ($setting) use ($settingKeys) {
                return isset($setting['name']) && in_array(strtolower($setting['name']), $settingKeys);
            });

            foreach ($eventSettings as $setting) {
                if (isset($setting['name']) && isset($setting['value'])) {
                    $checkinSettings[strtolower($setting['name'])] = $setting['value'] ?? 0;
                }
            }
            $this->updateRedis("checkin_settings:{$userGroup}", $this->event->code, json_encode($checkinSettings), config("app.times.seconds.five-minutes"));
            $checkinSettings = $this->getRedis("checkin_settings:{$userGroup}", $this->event->code, "array");
        }

        return $checkinSettings;
    }

    public function check()
    {
        $authUser = auth()->user();
        $defaultType = !empty($authUser) && $authUser->is_checkout ? Checkin::TYPE_CHECKOUT : Checkin::TYPE_CHECKIN;
        $type = in_array($this->attributes['type'] ?? null, [
            Checkin::TYPE_CHECKIN,
            Checkin::TYPE_CHECKOUT,
        ], true) ? $this->attributes['type'] : $defaultType;
        $responseType = $type === Checkin::TYPE_CHECKOUT ? "checkout" : "checkin";
        $successCountKey = $responseType === 'checkout' ? 'checkout_count' : 'checkin_count';
        $successNoDataKey = $responseType === 'checkout' ? 'checkout_no_data' : 'checkin_no_data';
        $duplicateErrorKey = $responseType === 'checkout' ? 'duplicate_checkout' : 'duplicate_checkin';
        // $this->event = $this->event()->findByAttributes([
        $this->event = Event::where([
            'code' => $this->eventCode,
        ])->first();

        if (empty($this->event)) return [
            'checkin'       => false,
            'is_duplicated' => false,
            'msg'           => __("responses.{$responseType}.errors.event_not_found", [
                'code'      => $this->eventCode
            ]),
        ];

        // $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $settings = $this->getEventSettings();
        $customCheckinMessages = $this->getCustomMessages();
        // $customCheckinMessages = $this->event->custom_checkin_messages ? json_decode($this->event->custom_checkin_messages, true) : [];
        // $customCheckinMessages = count($customCheckinMessages) && isset($customCheckinMessages[strtolower($userGroup)]) ? $customCheckinMessages[strtolower($userGroup)] : [];

        if (empty($settings) || $settings['allow_checkin_nodata']) {
            $checkin = $this->storeCheckin(null, [], $type);
            if ($checkin) {
                return [
                    'checkin'       => true,
                    'is_duplicated' => false,
                    'msg'           => $settings['show_checkin_count'] ? __("responses.{$responseType}.successes.{$successCountKey}", [
                        'count'     => $checkin->checkin_count,
                    ]) : $this->resolveResponseMessage(
                        $responseType,
                        "responses.{$responseType}.successes.{$successNoDataKey}",
                        $customCheckinMessages['success']['msg'] ?? null
                    ),
                    'count'         => $settings['show_checkin_count'] ? $checkin->checkin_count : null,
                    'model'         => $checkin,
                ];
            }
        }

        /* Find client */
        // $client = $this->client()->findByAttributes([
        //     'event_id'  => $this->event->id,
        //     'qrcode'    => $this->qrcode
        // ]);
        $client = Client::where([
                'event_id'  => $this->event->id,
                'qrcode'    => $this->qrcode
            ])
            ->whereIn('status', [
                Client::STATUS_ACTIVE,
                Client::STATUS_NEW,
            ])
            ->first();

        if (empty($client)) {
            /* customize */
            /* aura */
            if ($this->event->code == "aura") {
                $checkin = $this->storeCheckin(null, [], $type);
                if ($checkin) {
                    return [
                        'checkin'       => true,
                        'is_duplicated' => false,
                        'msg'           => $settings['show_checkin_count'] ? __("responses.{$responseType}.successes.{$successCountKey}", [
                            'count'     => $checkin->checkin_count,
                        ]) : $this->resolveResponseMessage(
                            $responseType,
                            "responses.{$responseType}.successes.{$successNoDataKey}",
                            $customCheckinMessages['success']['msg'] ?? null
                        ),
                        'count'         => $settings['show_checkin_count'] ? $checkin->checkin_count : null,
                        'model'         => $checkin,
                    ];
                }
            }

            return [
                'checkin'       => false,
                'is_duplicated' => false,
                'msg'           => $this->resolveResponseMessage(
                    $responseType,
                    "responses.{$responseType}.errors.client_not_found",
                    $customCheckinMessages['failed']['msg'] ?? null,
                    [
                        'qrcode'    => $this->qrcode,
                    ]
                ),
            ];
        }

        $skipCheckForDuplicate = false;

        /* theo khu vực */
        /* customize */
        /* galaxy-holding */
        if (!empty(auth()->user()->area_id)) {
            if (in_array($client->type, auth()->user()->area->client_types)) {
                /* CHECK FOR CHECKIN DUPLICATE ON AREA */

                /* customize */
                /* hidec-2025 */
                if ($this->event->code == "hidec-2025") {
                    $hople = false;

                    if ($client) {
                        $tickets = [
                            'tien_hoi_nghi'     => [
                                'name'          => 'Tiền Hội nghị - Phẫu thuật hàm mặt',
                                'dates'         => [
                                    '07-11-2025',
                                ],
                            ],
                            'hoi_nghi_chinh_thuc_sv'     => [
                                'name'          => 'Hội nghị chính thức (Sinh viên)',
                                'dates'         => [
                                    '08-11-2025',
                                    '09-11-2025',
                                ],
                            ],
                            'hoi_nghi_chinh_thuc'     => [
                                'name'          => 'Hội nghị chính thức',
                                'dates'         => [
                                    '08-11-2025',
                                    '09-11-2025',
                                ],
                            ],
                            'hands_on_cung_gs_lisa'     => [
                                'name'          => 'Hands-on cùng GS. Lisa Heitz-Mayfield',
                                'dates'         => [
                                    '08-11-2025',
                                ],
                            ],
                            'gala_dinner'     => [
                                'name'          => 'Gala Dinner',
                                'dates'         => [
                                    '08-11-2025',
                                ],
                            ],
                            'hoi_nghi_ngay_2'     => [
                                'name'          => 'Hội nghị ngày 2',
                                'dates'         => [
                                    '09-11-2025',
                                ],
                            ],
                            'dentium_ly_thuyet'     => [
                                'name'          => 'Dentium - Lý thuyết',
                                'dates'         => [
                                    '07-11-2025',
                                ],
                            ],
                            'dentium_ly_thuyet_thuc_hanh'     => [
                                'name'          => 'Dentium - Lý thuyết & Thực hành',
                                'dates'         => [
                                    '07-11-2025',
                                ],
                            ],
                        ];

                        $clientTicketCodes = $client->custom_fields['tickets'] ?? [];

                        foreach ($clientTicketCodes as $ticketCode) {
                            $ticket = $tickets[$ticketCode] ?? collect($tickets)->firstWhere('name', $ticketCode);
                            $dates = $ticket['dates'] ?? [];
                            foreach ($dates as $date) {
                                $validDates[] = $date;
                            }
                        }

                        $today = now()->format('d-m-Y');
                        $isTodayValid = in_array($today, $validDates, true);

                        if (!$isTodayValid) {
                            return [
                                'checkin'       => false,
                                'is_duplicated' => true,
                                'msg'           => "Qrcode không hợp lệ",
                                'model'         => null,
                                'count'         => 0,
                                'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                            ];
                        }
                    }
                }
                /* end */
                /* hidec HNT4 */
                if ($this->event->code == "hnt4") {
                    $hople = false;

                    if ($client) {
                        $tickets = [
                            'tien_hoi_nghi'     => [
                                'name'          => 'TIỀN HỘI NGHỊ',
                                'dates'         => [
                                    '05-04-2026',
                                ],
                            ],
                            'ngay_1'     => [
                                'name'          => 'NGÀY 1',
                                'dates'         => [
                                    '06-04-2026',
                                ],
                            ],
                            'ngay_2'     => [
                                'name'          => 'NGÀY 2',
                                'dates'         => [
                                    '07-04-2026',
                                ],
                            ],
                        ];

                        $clientTicketCodes = $client->custom_fields['tickets'] ?? [];

                        foreach ($clientTicketCodes as $ticketCode) {
                            $ticket = $tickets[$ticketCode] ?? collect($tickets)->firstWhere('name', $ticketCode);
                            $dates = $ticket['dates'] ?? [];
                            foreach ($dates as $date) {
                                $validDates[] = $date;
                            }
                        }

                        $today = now()->format('d-m-Y');
                        $isTodayValid = in_array($today, $validDates, true);

                        if (!$isTodayValid) {
                            return [
                                'checkin'       => false,
                                'is_duplicated' => false,
                                'msg'           => "QRCODE KHÔNG HỢP LỆ<br>(Vé không thuộc ngày hôm nay)",
                                'model'         => null,
                                'count'         => 0,
                                'client'        => $client,
                            ];
                        }
                    }
                }

                /* hidec HNT4 AN TRUA */
                if ($this->event->code == "hnt4-an-trua") {
                    if ($client) {
                        $tickets = [
                            'tien_hoi_nghi'     => [
                                'name'          => 'TIỀN HỘI NGHỊ',
                                'dates'         => [
                                    '05-04-2026',
                                ],
                            ],
                            'ngay_1'     => [
                                'name'          => 'NGÀY 1',
                                'dates'         => [
                                    '06-04-2026',
                                ],
                            ],
                            'ngay_2'     => [
                                'name'          => 'NGÀY 2',
                                'dates'         => [
                                    '07-04-2026',
                                ],
                            ],
                        ];

                        $validDates = [];
                        $clientTicketCodes = $client->custom_fields['tickets'] ?? [];

                        foreach ($clientTicketCodes as $ticketCode) {
                            $ticket = $tickets[$ticketCode] ?? collect($tickets)->firstWhere('name', $ticketCode);
                            $dates = $ticket['dates'] ?? [];
                            foreach ($dates as $date) {
                                $validDates[] = $date;
                            }
                        }

                        $today = now()->format('d-m-Y');
                        $isTodayValid = in_array($today, $validDates, true);

                        // 1. Kiểm tra ngày hợp lệ giống "hnt4"
                        if (!$isTodayValid) {
                            return [
                                'checkin'       => false,
                                'is_duplicated' => false,
                                'msg'           => "QRCODE KHÔNG HỢP LỆ (Vé không thuộc ngày hôm nay)",
                                'model'         => null,
                                'count'         => 0,
                                'client'        => $client,
                            ];
                        }

                        // 2. Thêm logic kiểm tra chỉ cho phép checkin ĐÚNG 1 LẦN DUY NHẤT
                        $checkin = Checkin::where([
                            'event_id'  => $this->event->id,
                            'event_code'    => $this->event->code,
                            'qrcode'    => $this->qrcode,
                            'type'      => $type,
                        ])->first();

                        if ($checkin) {
                            // Nếu đã tìm thấy lịch sử check-in, lập tức báo lỗi Duplicate
                            return [
                                'checkin'       => false,
                                'is_duplicated' => true,
                                'msg'           => "QRCODE ĐÃ CHECKIN",
                                'model'         => $checkin,
                                'count'         => $checkin->checkin_count ?? null,
                                'client'        => !empty($customCheckinMessages['duplicated']['show_info']) ? $client : null,
                            ];
                        }

                        // 3. Nếu chưa từng check-in, gán các biến cờ này thành true để qua mặt bước kiểm tra 
                        // duplicateặc định (tránh sinh thêm mã trùng lặp ngoài ý muốn) ở phía cuối hàm `check()`
                        $this->byPassDuplicate = true;
                        $skipCheckForDuplicate = true;
                    }
                }


                /* midea-viet-nam-2811 */
                if ($this->event->code == "midea-viet-nam-2811") {

                    // lấy loại thiệp
                    $loaiThiep = $client->custom_fields['loai_thiep'] ?? null;
                    $mideaThiepDate = [
                        'THIEP_27' => [
                           'dates' => ['2025-11-27'],
                        ],
                        'THIEP_28' => [
                           'dates' => ['2025-11-28'],
                        ],
                        'THIEP_27_28' => [
                           'dates' => ['2025-11-27','2025-11-28'],
                        ],
                    ];

                    $today = now()->format('Y-m-d');
                    $validDates = $mideaThiepDate[$loaiThiep]['dates'] ?? [];

                    if (!in_array($today, $validDates, true)) {
                        return [
                            'checkin'       => false,
                            'is_duplicated' => true,
                            'msg'           => "CHECKIN THẤT BẠI",
                            'model'         => null,
                            'count'         => 0,
                            'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                        ];
                    } else {
                        $checkin = Checkin::where([
                            'event_id'  => $this->event->id,
                            'qrcode'    => $this->qrcode,
                            'type'      => $type,
                        ])
                            ->whereDate('scan_time', today())
                            ->first();

                        if ($checkin) {
                            return [
                                'checkin'       => false,
                                'is_duplicated' => true,
                                'msg'           => $this->resolveResponseMessage(
                                    $responseType,
                                    "responses.{$responseType}.errors.{$duplicateErrorKey}",
                                    $customCheckinMessages['duplicated']['msg'] ?? null
                                ),
                                'model'         => $checkin,
                                'count'         => $checkin->checkin_count ?? null,
                                'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                            ];
                        }

                        $this->byPassDuplicate = true;
                        $skipCheckForDuplicate = true;
                    }
                }


                if ($settings['no_duplicate_checkin'] && !$this->byPassDuplicate) {
                    $userId = auth()->user()->id;
                    $userIds = auth()->user()->area->users->pluck('id')->toArray();

                    $checkin = Checkin::where([
                        'event_id'  => $this->event->id,
                        'qrcode'    => $this->qrcode,
                        'type'      => $type,
                    ])->first();

                    if ($checkin) {
                        if (in_array($userId, $userIds)) {
                            $checkin->where([
                                'user_id'   => $userId,
                            ]);
                        } else {
                            $checkin->whereIn('user_id', $userIds);
                        }

                        return [
                            'checkin'       => false,
                            'is_duplicated' => true,
                            'msg'           => $this->resolveResponseMessage(
                                $responseType,
                                "responses.{$responseType}.errors.{$duplicateErrorKey}",
                                $customCheckinMessages['duplicated']['msg'] ?? null
                            ),
                            'model'         => $checkin,
                            'count'         => $checkin->checkin_count ?? null,
                            'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                        ];
                    }

                    $skipCheckForDuplicate = true;
                }
            } else {
                return [
                    'checkin'       => false,
                    'is_duplicated' => false,
                    'msg'           => $this->resolveResponseMessage(
                        $responseType,
                        "responses.{$responseType}.errors.client_not_found",
                        $customCheckinMessages['failed']['msg'] ?? null,
                        [
                            'qrcode'    => $this->qrcode,
                        ]
                    ),
                ];
            }
        }

        /* CHECK FOR CHECKIN DUPLICATE */
        if (!$skipCheckForDuplicate) {
            if ($settings['no_duplicate_checkin'] && !$this->byPassDuplicate) {
                $checkin = Checkin::where([
                    'event_id'  => $this->event->id,
                    'qrcode'    => $this->qrcode,
                    'type'      => $type,
                ])->first();

                if ($checkin) {
                    return [
                        'checkin'       => false,
                        'is_duplicated' => true,
                        'msg'           => $this->resolveResponseMessage(
                            $responseType,
                            "responses.{$responseType}.errors.{$duplicateErrorKey}",
                            $customCheckinMessages['duplicated']['msg'] ?? null
                        ),
                        'model'         => $checkin,
                        'count'         => $checkin->checkin_count ?? null,
                        'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                    ];
                }
            }
        }

        $checkin = $this->storeCheckin($client, $this->attributes['custom_fields'], $type);

        if ($checkin) {
            return [
                'checkin'       => true,
                'is_duplicated' => false,
                'msg'           => $settings['show_checkin_count'] ? __("responses.{$responseType}.successes.{$successCountKey}", [
                    'count'     => $checkin->checkin_count,
                ]) : $this->resolveResponseMessage(
                    $responseType,
                    "responses.{$responseType}.success",
                    $customCheckinMessages['success']['msg'] ?? null
                ),
                'count'         => $checkin->checkin_count ?? null,
                'model'         => $checkin,
                'client'        => isset($customCheckinMessages['success']['show_info']) ? $client : null,
                // 'client'        => $client,
            ];
        }
    }

    private function resolveResponseMessage(string $responseType, string $translationKey, ?string $customMessage = null, array $replace = []): string
    {
        if ($responseType !== 'checkout' && !empty($customMessage)) {
            return $customMessage;
        }

        return __($translationKey, $replace);
    }

    public function storeCheckin($modelClient = null, $customFields = [], $type = Checkin::TYPE_CHECKIN)
    {
        $attributes = [
            'event_id'      => $this->event->id,
            'event_code'    => $this->event->code,
            'user_id'       => auth()->user()->id ?? null,
            // 'device_name'   => auth()->user()->username ?? null,
            'client_name'   => (!empty($modelClient) && !empty($modelClient->name)) ? $modelClient->name : Checkin::NO_DATA_NAME,
            'qrcode'        => $this->qrcode,
            'scan_time'     => $this->scanTime,
            'type'          => $type,
            'status'        => Checkin::STATUS_NEW,
            'custom_fields' => json_encode($customFields),
        ];

        if ($model = $this->create($attributes)){
            if (!empty(auth()->user()->area_id)) {
                if (in_array($modelClient->type, auth()->user()->area->client_types)) {
                    $userId = auth()->user()->id;
                    $userIds = auth()->user()->area->users->pluck('id')->toArray();
                    if (in_array($userId, $userIds)) {
                        $userId = $userIds;
                    }
                }
            }

            $model->checkin_count = $this->getListByAttributes(
                array_filter([
                    'event_id'  => $this->event->id,
                    'qrcode'    => $this->qrcode,
                    'type'      => $type,
                    'user_id'   => $userId ?? null,
                ])
            )->count();
            return $model;
        }

        return null;
    }
}
