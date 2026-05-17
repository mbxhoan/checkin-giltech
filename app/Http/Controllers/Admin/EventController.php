<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\EventDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\CloneRequest;
use App\Http\Requests\Admin\Events\EventsRequest;
use App\Http\Requests\Admin\Events\ListRequest;
use App\Http\Requests\Admin\Events\RemoveFeatureRequest;
use App\Http\Requests\Admin\Events\UpdateCustomCheckinMessageRequest;
use App\Http\Requests\Admin\Events\UpdateFeatureRequest;
use App\Http\Requests\Admin\Events\UploadMediaRequest;
use App\Models\Client;
use App\Services\Admin\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventSetting;

class EventController extends Controller
{
    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application events index.
     */
    public function index(EventDataTable $dataTable, ListRequest $request)
    {
        if (auth()->user()->isSysAdmin()) {
            $companys = $this->service->company()->getListByAttributes([
                'status'    => [
                    Company::STATUS_ACTIVE,
                ],
            ]);
        }

        $total = $dataTable->getFilter();

        return $dataTable->render('admin.events.index', [
            'companyArray'          => !empty($companys) ? $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray() : [],
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'total'                 => $total->count(),
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function create(): View
    {
        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
        }

        return view('admin.events.detail', [
            'model'         => $this->service->init(),
            'company'       => $company ?? null,
            'companyArray'  => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
        ]);
    }

     /**
     * Display the specified resource edit form.
     */
    public function edit(Event $event)
    {
        $this->authorize('edit', $event);

        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
        }

        /* for event areas */
        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'type'     => $key,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        return view('admin.events.detail', [
            'clientTypes'           => $types,
            'model'                 => $event,
            'company'               => $company ?? null,
            'companyArray'          => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                    'event_id' => $event->id
                ], [], [], 0, [
                    'order' => 'ASC',
                ]),
            'setting'               => $this->service->event_setting()->init(),
            'settings'              => $this->service->event_setting()->getListByAttributes([
                    'event_id'      => $event->id,
                    'status'        => [
                        EventSetting::STATUS_ACTIVE
                    ]
                ], [], [], 0, [
                    'id'            => 'ASC',
                    'group'         => 'ASC',
                    'input_type'    => 'DESC',
                    // 'updated_at'    => 'DESC'
                ]),
            'eventFiles'            => $this->service->event_file()->getListByAttributes([
                    'event_id' => $event->id
                ], [], [], 0, [
                    'created_at'    => 'DESC',
                ]),
            'landingPages'          => $this->service->landing_page()->getListByAttributes([
                    'event_id'      => $event->id
                ], [], [], 0, []),
            'cards'                 => $this->service->card()->getListByAttributes([
                    'event_id'      => $event->id
                ], [], [], 0, []),
            'labels'                => $this->service->label()->getListByAttributes([
                    'event_id'      => $event->id
                ], [], [], 0, []),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventsRequest $request): RedirectResponse
    {
        $attributes = $request->only(['company_id', 'province_id', 'code', 'name', 'status', 'from_date', 'to_date', 'description', 'features']);
        $attributes['code'] = Event::generateUniqueEventCode($attributes['name']);
        $attributes['features'] = json_encode($attributes['features'] ?? []);
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $event = $this->service->create($attributes);

        /* init custom fields template */
        $this->service->custom_field_template()->initByEvent($event);
        $this->service->event_setting()->syncByEvent($event);
        return redirect()->route('admin.events.edit', $event)->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventsRequest $request, Event $event): RedirectResponse
    {
        $attributes = $request->only(['company_id', 'province_id', 'name', 'status', 'from_date', 'to_date', 'description']);
        // $attributes['features'] = json_encode($attributes['features'] ?? []);
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = $request->status == Event::STATUS_NEW ? Event::STATUS_ACTIVE : $request->status;
        $this->service->update($event->id, $attributes);

        $logo = $request->file('logo');

        if ($logo) {
            $this->service->attributes['image'] = $logo;
            $this->service->attributes['name'] = $logo->getClientOriginalName();

            if ($result = $this->service->mediaLibraryService()->store()) {
                if (!empty($result['media'])) {
                    $this->service->update($event->id, [
                        'logo' => $result['media']->id
                    ]);

                    if ($event->logo) {
                        $this->service->mediaLibraryService()->deleteMedia($event->logo);
                    }
                } else {
                    return redirect()->route('admin.events.edit', $event)->withErrors($result['msg']);
                }
            }
        }

        return redirect()->route('admin.events.edit', $event)->withSuccess("Cập nhật thành công");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->service->deleteOnStatus($event->id);
        return redirect()->route('admin.events.index')->withSuccess(__('events.deleted'));
    }

    public function uploadMedias(UploadMediaRequest $request, Event $event)
    {
        $uploadedSomething = false;
        $files = $request->only([
            'main_bg_desktop',
            'main_bg_mobile',
            'logo',
            'favicon',
        ]);

        $sounds = $request->only([
            'sound_success',
            'sound_fail',
        ]);

        // if (count($sounds)) {
        //     foreach ($sounds as $key => $sound) {
        //         $path = $sound->store('sounds', 'public');
        //         $this->service->update($event->id, [
        //             $key => "medias/{$path}"
        //         ]);
        //     }

        //     $uploadedSomething = true;
        // }

        if (count($files)) {
            foreach ($files as $key => $file) {
                if ($file) {
                    $this->service->attributes['image'] = $file;

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($event->id, [
                                $key => $result['media']->id
                            ]);

                            if ($event->$key) {
                                $this->service->mediaLibraryService()->deleteMedia($event->$key);
                            }
                        } else {
                            return back()->withErrors($result['msg']);
                        }
                    }
                }
            }

            $uploadedSomething = true;
        }

        if (!$uploadedSomething) {
            return back()->withErrors('Không thể xử lý file');
        }

        return back()->withSuccess('Nạp file thành công');
    }

    /**
     * Display the specified resource edit form.
     */
    public function getListByCompanyId($companyId)
    {
        $events = $this->service->getListByAttributes([
            'company_id' => $companyId,
        ]);

        if (!empty($events)) {
            return $this->responseSuccess([
                'list' => $events,
            ]);
        }

        return $this->responseError("Không tìm thấy thông tin công ty {$companyId}");
    }

    public function updateCustomCheckinMessages(Event $event, UpdateCustomCheckinMessageRequest $request)
    {
        $customCheckinMessages = $request->custom_checkin_messages;
        $defaultCustomCheckinMessages = $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [];

        // foreach ($checkins as $screen => $configs) {
        foreach ($customCheckinMessages as $screen => $customCheckinMessageAttrs) {
            foreach ($customCheckinMessageAttrs as $msg => $attrs) {
                /* set for boolean columns */
                foreach ([
                    'bold',
                    'italic',
                    'underline',
                    'bg',
                    'show_info',
                ] as $field) {
                    if (isset($attrs[$field])) {
                        $attrs[$field] = (($attrs[$field] == "true" || $attrs[$field] == "1") ? 1 : 0);
                    } else {
                        $attrs[$field] = 0;
                    }

                    if (in_array($field, [
                        'show_info'
                    ])) {
                        if (in_array($msg, [
                            'success',
                        ])) {
                            $attrs[$field] = true;
                        }
                    }
                }

                $defaultCustomCheckinMessages[$screen][$msg] = $attrs;
            }

            if (count($defaultCustomCheckinMessages)) {
                $this->service->update($event->id, [
                    'custom_checkin_messages' => $defaultCustomCheckinMessages,
                ]);
            }
        }

        return $this->responseSuccess(null, "Đã cập nhật");
    }

    public function clone(CloneRequest $request, Event $event)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:COPY'],
        ]);

        $newEvent = $this->service->cloneModel($event, [
            'ref_id'        => $event->id,
            'company_id'    => $request->company_id,
            // 'code'          => $request->code,
            'code'          => Event::generateUniqueEventCode($request->name),
            'name'          => $request->name,
            'from_date'     => $request->from_date,
            'to_date'       => $request->to_date,
            'status'        => Event::STATUS_NEW,
            'created_by'    => auth()->user()->id,
            'updated_by'    => auth()->user()->id,
        ]);

        /* clone custom fields template */
        $customFieldTemplates = $event->custom_field_templates;
        foreach ($customFieldTemplates as $customFieldTemplate) {
            $this->service->custom_field_template()->cloneModel($customFieldTemplate, [
                'event_id' => $newEvent->id,
            ]);
        }

        /* clone settings */
        $settings = $event->getEventSettings();
        foreach ($settings as $setting) {
            $this->service->event_setting()->cloneModel($setting, [
                'event_id' => $newEvent->id,
            ]);
        }

        return redirect()->route('admin.events.index')
            ->withSuccess("Đã nhân bản sự kiện {$newEvent->code}");
    }

    public function updateFeatures(UpdateFeatureRequest $request, Event $event)
    {
        $attributes['features'] = json_encode($request->features);
        $this->service->update($event->id, $attributes);
        return redirect()->route('admin.events.edit', $event)
            ->withSuccess("Cập nhật tính năng thành công");
    }

    public function removeFeature(RemoveFeatureRequest $request, Event $event)
    {
        $features = json_decode($event->features, true);
        // dd($features, $request->feature);

        if ($event->hasFeature($request->feature)) {
            $toRemoveFeature = $request->feature;
            $features = array_values(array_filter($features, fn($item) => $item !== $toRemoveFeature));
        }

        $this->service->update($event->id, [
            'features' => $features
        ]);

        return redirect()->route('admin.events.edit', $event)
            ->withSuccess("Đã ẩn tính năng thành công");
    }
}
