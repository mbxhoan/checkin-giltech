<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\LandingPageDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPages\CloneRequest;
use App\Http\Requests\Admin\LandingPages\ListRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use App\Http\Requests\Admin\LandingPagesRequest;
use App\Models\Campaign;
use App\Models\Card;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\LandingPage;
use App\Services\Admin\LandingPageService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __construct(LandingPageService $service)
    {
        $this->service = $service;
    }

    public function index(ListRequest $request)
    {
        $dataTable = new LandingPageDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();
        $clients = $this->service->client()->getListByAttributes([
            'event_id'      => $events->pluck('id')->toArray(),
        ]);
        $clientsLp = (clone $clients)->where('register_source', Client::REGISTER_LP);
        $totalAccesses = $this->service->page_access_log()->getListByAttributes([
            'lp_id' => $total->get()->pluck('id')->toArray()
        ]);

        return $dataTable->render('admin.landing_pages.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
            'clientsLp'         => $clientsLp,
            'totalAccesses'     => $totalAccesses,
        ]);
    }

    public function selectEventToCreate(SelectEventToCreateRequest $request)
    {
        return redirect()->route('admin.landing_pages.create', [
            'event' => $request->event_id
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(LandingPage $landing_page)
    {
        $this->authorize('edit', $landing_page);

        // if (!$landing_page->checkIfLandingPageIsValid()) {
        //     return back()->withErrors(__('auth.not_authorized'));
        // }

        $events = $this->service->event()->getEventList();
        $languages = $landing_page->getLanguages();
        $currentLanguageCode = !empty(request()->lang) ?
            ((!empty($languages) && !empty($languages->firstWhere('code', request()->lang))) ? request()->lang :
            app()->getLocale()) :
            app()->getLocale();

        if ($languages->contains('code', $currentLanguageCode)) {
            $languages = $languages->sortBy(function ($language) use ($currentLanguageCode) {
                return $language->code === $currentLanguageCode ? 0 : 1;
            })->values();
        }

        $language = !empty($languages) ? $languages->first() : null;
        $registerSendMail = $landing_page->event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? false;

        if ($registerSendMail) {
            $campaigns = $landing_page->event->campaigns->where('status', '!=', Campaign::STATUS_DELETED);
            $campaignArray = ["" => "-"] + $campaigns->pluck('name', 'id')->toArray();
        }

        $cards = $landing_page->event->cards->where('status', '!=', Card::STATUS_DELETED);
        $cardArray = ["" => "-"] + $cards->pluck('code', 'id')->toArray();

        return view('admin.landing_pages.detail', [
            'model'                 => $landing_page,
            'event'                 => $landing_page->event,
            'client'                => $this->service->client()->init(),
            'language'              => $language ?? $this->service->language()->init(),
            'languages'             => $landing_page->event->company->getLanguages(),
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            // 'customFieldTemplates'  => $event->getCustomFieldTemplates(true, true),
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                    'event_id'      => $landing_page->event->id,
                    // 'is_lp'         => true,
                ], [], [], 0, [
                    'order'         => 'ASC',
                ]),
            'registerSendMail'      => $registerSendMail,
            'campaignArray'         => $campaignArray ?? [],
            'cardArray'             => $cardArray ?? [],
            'settings'              => $this->service->event_setting()->getListByAttributes([
                'event_id'          => $landing_page->event->id,
                'group'             => EventSetting::GROUP_LP,
                'status'            => [
                    EventSetting::STATUS_ACTIVE
                ]
            ]),
            'eventArray'            => $events->mapWithKeys(function ($event) {
                    return [$event->id  => "{$event->code} - {$event->name}"];
                })->toArray(),
            'openForm'              => $landing_page->event->getEventSetting("ENABLE_FORM", null)->value ?? false,
            'openCaptcha'           => $landing_page->event->getEventSetting("ENABLE_CAPTCHA", null)->value ?? false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        $this->authorize('create_landing_page', $event);

        $registerSendMail = $event->getEventSetting("REGISTER_SEND_EMAIL", null)->value ?? false;

        if ($registerSendMail) {
            $campaigns = $event->campaigns->where('status', '!=', Campaign::STATUS_DELETED);
            $campaignArray = ["" => "-"] + $campaigns->pluck('name', 'id')->toArray();
        }

        $cards = $event->cards->where('status', '!=', Card::STATUS_DELETED);
        $cardArray = ["" => "-"] + $cards->pluck('code', 'id')->toArray();

        if ($event->getEventSetting("OPEN_LANDING_PAGE", null)->value ?? null) {
            return view('admin.landing_pages.detail', [
                'model'                 => $this->service->init(),
                'languages'             => $event->company->getLanguages(),
                'event'                 => $event,
                'registerSendMail'      => $registerSendMail,
                'campaignArray'         => $campaignArray ?? [],
                'cardArray'             => $cardArray ?? [],
            ]);
        }

        return back()->withErrors(__('auth.not_authorized'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LandingPagesRequest $request): RedirectResponse
    {
        $landingPage = $this->service->init();

        $attributes = $request->only([
            'template_id',
            'event_id',
            'slug',
            'align',
            'form_width',
            'languages',
            'status',
            'contact_name',
            'contact_email',
            'contact_phone',
            'contact_address',
            'campaign_ids',
            'card_ids',
        ]);

        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        if (isset($attributes['languages'])) {
            $attributes['languages'] = json_encode($attributes['languages']);
        }
        $landingPage = $this->service->create($attributes);

        if (isset($attributes['campaign_ids'])) {
            $landingPage->syncCampaignsByLang($attributes['campaign_ids']);
        }

        if (isset($attributes['card_ids'])) {
            $landingPage->syncCardsByLang($attributes['card_ids']);
        }

        $medias = $request->only(array_keys($landingPage->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($request->hasFile($key) && $request->file($key)->isValid()) {
                    if ($media) {
                        $this->service->attributes['image'] = $media;
                        $this->service->attributes['name'] = $media->getClientOriginalName();

                        if ($result = $this->service->mediaLibraryService()->store()) {
                            if (!empty($result['media'])) {
                                $this->service->update($landingPage->id, [
                                    $key => $result['media']->id
                                ]);
                            } else {
                                return redirect()->route('admin.landing_pages.edit', [
                                    'event'         => $landingPage->event,
                                    'landing_page'  => $landingPage,
                                ])->withErrors($result['msg']);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.landing_pages.edit', [
            'event'         => $landingPage->event,
            'landing_page'  => $landingPage,
        ])->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LandingPagesRequest $request, LandingPage $landing_page): RedirectResponse
    {
        // if (!$landing_page->checkIfLandingPageIsValid()) {
        //     return back()->withErrors(__('auth.not_authorized'));
        // }

        $attributes = $request->only([
            'template_id',
            'event_id',
            'slug',
            'align',
            'form_width',
            'languages',
            'status',
            'contact_name',
            'contact_email',
            'contact_phone',
            'contact_address',
            'campaign_ids',
            'card_ids',
        ]);

        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($landing_page->id, $attributes);

        if (isset($attributes['campaign_ids'])) {
            $landing_page->syncCampaignsByLang($attributes['campaign_ids']);
        }

        if (isset($attributes['card_ids'])) {
            $landing_page->syncCardsByLang($attributes['card_ids']);
        }

        $medias = $request->only(array_keys($landing_page->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($request->hasFile($key) && $request->file($key)->isValid()) {
                    if ($media) {
                        $this->service->attributes['image'] = $media;
                        $this->service->attributes['name'] = $media->getClientOriginalName();

                        if ($result = $this->service->mediaLibraryService()->store()) {
                            if (!empty($result['media'])) {
                                $this->service->update($landing_page->id, [
                                    $key => $result['media']->id
                                ]);

                                if ($landing_page->$key) {
                                    $this->service->mediaLibraryService()->deleteMedia($landing_page->$key);
                                }
                            } else {
                                return redirect()->route('admin.landing_pages.edit', [
                                    'event'         => $landing_page->event,
                                    'landing_page'  => $landing_page,
                                ])->withErrors($result['msg']);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.landing_pages.edit', [
            'event'         => $landing_page->event,
            'landing_page'  => $landing_page,
        ])->withSuccess("Cập nhật thành công");
    }

    public function updateShowLanguageSelection(Request $request, LandingPage $landing_page)
    {
        $attributes['show_language_selection'] = false;

        if (!empty($request->is_show) && $request->is_show == "true") {
            $attributes['show_language_selection'] = true;
        }

        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($landing_page->id, $attributes);
        return $this->responseSuccess(null, "Cập nhật thành công");
    }

    public function clone(CloneRequest $request, LandingPage $landing_page)
    {
        /* clone landing_page */
        $newLp = $landing_page->replicate();
        $newLp->event_id   = $request->event_id;
        $newLp->slug       = $request->name;
        $newLp->created_by = auth()->user()->id;
        $newLp->updated_by = auth()->user()->id;
        $newLp->status     = LandingPage::STATUS_ACTIVE;
        $newLp->save();
        return redirect()->route('admin.landing_pages.edit', $newLp)
            ->withSuccess("Đã nhân bản thành công");
    }

    public function renderIphonePreview()
    {

    }

    public function destroy(LandingPage $landingPage){
        try{
            $landingPage->event()->touch();

            $landingPage->landingPageCampaigns()->delete();
            $landingPage->landingPageCards()->delete();
            $landingPage->delete();

            return $this->responseSuccess(null, "Đã xóa thành công {$landingPage->slug}");
        } catch (\Exception){
            return $this->responseError("Không thể xóa trang Landing page");
        }
    }
}
