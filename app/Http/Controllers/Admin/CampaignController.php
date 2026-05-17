<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Admin\CampaignDataTable;
use App\DataTables\Admin\CampaignDetailDataTable;
use App\Http\Requests\Admin\Campaigns\CloneRequest;
use App\Http\Requests\Admin\Campaigns\ListRequest;
use App\Http\Requests\Admin\Campaigns\StoreRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use App\Services\Admin\CampaignService;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\CampaignDetail;
use App\Models\Client;
use App\Models\Email;
use App\Models\Event;

class CampaignController extends Controller
{
    public function __construct(CampaignService $service)
    {
        $this->service = $service;
    }

    public function viewHistory(Campaign $campaign)
    {
        $this->authorize('view_history', $campaign);

        $emails = $campaign->getEmails([
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
                Email::STATUS_CLOSED,
            ])
            ->orderBy('status', 'DESC')
            ->get();

        $emailsSent = $campaign->emails()
            ->where('sent_at', '!=', null)
            ->get();

        return view('admin.emails.history', [
            'campaign'      => $campaign,
            'emails'        => $emails,
            'emailsSent'    => $emailsSent,
        ]);
    }

    public function selectEventToCreate(SelectEventToCreateRequest $request)
    {
        return redirect()->route('admin.campaigns.create', [
            'event' => $request->event_id
        ]);
    }

    /**
     * Show the application products index.
     */
    public function index(ListRequest $request)
    {
        $dataTable = new CampaignDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
            if (!empty($company->limited_emails) && $company->limited_emails > 0) {
                $limitedEmails = $company->limited_emails;
            }

            $sentEmailCount = Email::whereNotNull('sent_at')
                    ->whereHas('campaign.event', function ($query) use ($company) {
                        $query->where('company_id', $company->id);
                    });
        } else {
            $sentEmailCount = $this->service->email()->getListByAttributes([], [
                'sent_at' => null,
            ]);
        }

        if (!empty($sentEmailCount)) {
            $dataStatuses = $this->service->postmark()->countByStatus($sentEmailCount->pluck('message_id')->toArray());
        }

        return $dataTable->render('admin.campaigns.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [$event->id  => "{$event->code} - {$event->name}"];
            })->toArray(),
            'sentEmailCount'    => !empty($sentEmailCount) ? $sentEmailCount->count() : null,
            'limitedEmails'     => $limitedEmails ?? null,
            'dataStatuses'      => $dataStatuses ?? [],
        ]);
    }

    public function create(Event $event)
    {
        $this->authorize('create_campaign', $event);

        $events = $this->service->event()->getEventList();

        if (!$event) {
            return redirect()->route('admin.campaigns.index')
                ->withErrors('Sự kiện không tồn tại');
        }

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $templates = $this->service->email_template()->getPostmarkTemplates()['Templates'] ?? [];
        $senders = $this->service->email_sender()->getPostmarkSenders()['SenderSignatures'] ?? [];

        return view('admin.campaigns.detail', [
            'model'                 => $this->service->init(),
            'event'                 => $event,
            'types'                 => $types,
            'templates'             => count($templates) ? collect($templates)
                ->pluck('Name', 'TemplateId')
                ->toArray() : [],
            'fromEmails'            => count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
                    return [$sender['EmailAddress'] => "{$sender['EmailAddress']} - {$sender['Name']}"];
                })
                ->toArray() : [],
            'fromNames'             => count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
                    return [$sender['EmailAddress'] => $sender['Name']];
                })
                ->toArray() : [],
            'eventArray'            => $events->mapWithKeys(function ($event) {
                return [$event->id  => "{$event->code} - {$event->name}"];
            })->toArray(),
        ]);
    }

    public function edit(Campaign $campaign)
    {
        $this->authorize('edit', $campaign);

        $events = $this->service->event()->getEventList();

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $campaign->event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $campaign->event->id,
                'status'   => [
                    Client::STATUS_ACTIVE,
                    Client::STATUS_NEW,
                ],
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $templates = $this->service->email_template()->getPostmarkTemplates()['Templates'] ?? [];
        $senders = $this->service->email_sender()->getPostmarkSenders()['SenderSignatures'] ?? [];

        $datas = [
            'model'                 => $campaign,
            'event'                 => $campaign->event,
            'types'                 => $types,
            'templates'             => count($templates) ? collect($templates)
                ->pluck('Name', 'TemplateId')
                ->toArray() : [],
            'template'              => $this->service->email_template()->getPostmarkTemplates($campaign->template_id),
            'fromEmails'            => count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
                    return [$sender['EmailAddress'] => "{$sender['EmailAddress']} - {$sender['Name']}"];
                })
                ->toArray() : [],
            'fromNames'             => count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
                    return [$sender['EmailAddress'] => $sender['Name']];
                })
                ->toArray() : [],
            'eventArray'            => $events->mapWithKeys(function ($event) {
                return [$event->id  => "{$event->code} - {$event->name}"];
            })->toArray(),
            'emailSending'          => $campaign->getEmails(Email::STATUS_WAITING),
            'emailCompleted'        => $campaign->getEmails(Email::STATUS_SENT),
            'emails'                => $campaign->getEmails([
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
            ])->get(),
            'emailErrors'           => $campaign->campaign_details->where('email_form', false)
                ->where('status', CampaignDetail::STATUS_ACTIVE),
        ];

        if ($campaign->campaign_details()->count()) {
            $dataTable = new CampaignDetailDataTable($campaign);
            return $dataTable->render('admin.campaigns.detail', $datas);
        }

        return view('admin.campaigns.detail', $datas);
    }

    public function store(StoreRequest $request)
    {
        $attributes = $request->only([
            'event_id',
            'template_id',
            'name',
            'type',
            'subject',
            'from_email',
            'from_name',
            'status',
            // 'cc',
            // 'bcc',
            'message_stream',
            'limitation_per_time',
            'hold_time',
            'is_online',
        ]);

        $ccArray = $this->service->convertEmailStringToArray($request->cc);
        $bccArray = $this->service->convertEmailStringToArray($request->bcc);
        $senders = $this->service->email_sender()->getPostmarkSenders()['SenderSignatures'] ?? [];
        $senders = count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
            return [$sender['EmailAddress'] => $sender['Name']];
        })
        ->toArray() : [];

        $attributes['from_name'] = $senders[$attributes['from_email']];
        $attributes['limitation_per_time'] = 5;
        $attributes['name'] = $attributes['event_id']."_".($attributes['type'] ?? "none")."_".date('YmdHis');
        $attributes['cc'] = json_encode($ccArray);
        $attributes['bcc'] = json_encode($bccArray);
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $campaign = $this->service->create($attributes);

        if ($campaign) {
            return redirect()->route('admin.campaigns.edit', $campaign)
                ->with('success', 'Tạo campaign thành công');
        }

        return back()
            ->withErrors('Tạo campaign thất bại');
    }

    public function update(StoreRequest $request, Campaign $campaign)
    {
        $attributes = $request->only([
            'event_id',
            'template_id',
            'name',
            'type',
            'subject',
            'from_email',
            'from_name',
            'status',
            // 'cc',
            // 'bcc',
            'message_stream',
            'limitation_per_time',
            'hold_time',
            'is_online',
        ]);

        $ccArray = $this->service->convertEmailStringToArray($request->cc);
        $bccArray = $this->service->convertEmailStringToArray($request->bcc);
        $senders = $this->service->email_sender()->getPostmarkSenders()['SenderSignatures'] ?? [];
        $senders = count($senders) ? collect($senders)->mapWithKeys(function ($sender) {
            return [$sender['EmailAddress'] => $sender['Name']];
        })
        ->toArray() : [];

        $attributes['from_name'] = $senders[$attributes['from_email']];
        $attributes['limitation_per_time'] = 5;
        $attributes['cc'] = json_encode($ccArray);
        $attributes['bcc'] = json_encode($bccArray);
        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($campaign->id, $attributes);

        if ($campaign) {
            return redirect()->route('admin.campaigns.edit', $campaign)
                ->with('success', 'Cập nhật campaign thành công');
        }

        return back()
            ->withErrors('Cập nhật campaign thất bại');
    }

    public function destroy(Campaign $campaign, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        $this->service->update($campaign->id, [
            'status' => Campaign::STATUS_DELETED,
        ]);

        return redirect()->route('admin.campaigns.index')
            ->withSuccess("Đã xoá campaign {$campaign->name}");
    }

    public function syncCampaignDetail(Campaign $campaign)
    {
        $result = $this->service->campaign_detail()->cloneClientByType($campaign);

        if ($result['status'] == false) {
            return back()->withErrors($result['message']);
        }

        return back()->withSuccess($result['message']);
    }

    public function getProgress(Campaign $campaign)
    {
        $this->authorize('view_progress', $campaign);

        return $this->responseSuccess([
            'html'          => view('components._progress', [
                'total'     => $campaign->getEmails([
                        Email::STATUS_NEW,
                        Email::STATUS_WAITING,
                        Email::STATUS_SENT,
                    ])
                    ->get()
                    ->count(),
                'completed' => $campaign->getEmails(Email::STATUS_SENT)->count(),
                'dataTime'  => 3,
                'dataEle'   => '#progress',
                'dataUrl'   => route('admin.campaigns.progress', $campaign),
            ])->render()
        ]);
    }

    public function getSendMailTable(Campaign $campaign)
    {
        $this->authorize('view_send_mail_table', $campaign);

        $emails = $campaign->getEmails([
            Email::STATUS_NEW,
            Email::STATUS_WAITING,
            Email::STATUS_SENT,
        ])
        ->get();

        return $this->responseSuccess([
            'html1'         => view('components._progress', [
                'total'     => $emails->count(),
                'completed' => $campaign->getEmails(Email::STATUS_SENT)->count(),
                'dataTime'  => 3,
                'dataEle'   => '#progress',
                'dataUrl'   => route('admin.campaigns.progress', $campaign),
            ])->render(),
            'html2'         => view('admin.emails.tables._sub-send-mail', [
                'emails'    => $emails,
            ])->render(),
        ]);
    }

    public function getHistoryTable(Campaign $campaign)
    {
        $this->authorize('view_history_table', $campaign);

        $emails = $campaign->getEmails([
                Email::STATUS_NEW,
                Email::STATUS_WAITING,
                Email::STATUS_SENT,
                Email::STATUS_CLOSED,
            ])
            ->orderBy('status', 'DESC')
            ->get();

        return $this->responseSuccess([
            'html1'         => null,
            'html2'         => view('admin.emails.tables._table-history', [
                'campaign'      => $campaign,
                'emails'        => $emails,
            ])->render(),
        ]);
    }

    public function clone(CloneRequest $request, Campaign $campaign)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:COPY'],
        ]);

        $newCampaign = $this->service->cloneModel($campaign, [
            'name'          => $campaign->event_id."_".($campaign->type ?? "none")."_".date('YmdHis'),
            'status'        => Campaign::STATUS_NEW,
            'created_by'    => auth()->user()->id,
            'updated_by'    => auth()->user()->id,
        ]);

        $result = $this->service->campaign_detail()->cloneClientByType($newCampaign);

        if ($result['status'] == false) {
            return redirect()->route('admin.campaigns.index')
                ->withSuccess("Đã nhân bản campaign {$newCampaign->name} ".$result['message']);
        }

        return redirect()->route('admin.campaigns.index')
            ->withSuccess("Đã nhân bản campaign {$newCampaign->name}");
    }
}
