<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Admin\CampaignDetailDataTable;
use App\Http\Requests\Admin\Campaigns\CreateRequest;
use App\Http\Requests\Admin\Campaigns\StoreRequest;
use App\Http\Requests\Admin\EmailSendersRequest;
use App\Services\Admin\EmailSenderService;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Email;

class EmailSenderController extends Controller
{
    public function __construct(EmailSenderService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application products index.
     */
    public function index()
    {
        $result = $this->service->getPostmarkSenders();
        return view('admin.email_senders.index', [
            'senders' => $result['SenderSignatures'],
            'total'   => $result['TotalCount'],
        ]);
    }

    public function create(CreateRequest $request)
    {

    }

    public function edit(int $senderId)
    {
        $sender = (object)$this->service->getPostmarkSender($senderId);
        return view('admin.email_senders.detail', [
            'sender' => $sender
        ]);
    }

    public function store(StoreRequest $request)
    {

    }

    public function update(int $senderId, EmailSendersRequest $request)
    {
        $attributes = $request->only([
            'name',
        ]);

        $sender = $this->service->updatePostmarkSender($senderId, $attributes);

        if ($sender) {
            return back()->withSuccess('Cập nhật thành công');
        }

        return back()->withErrors('Cập nhật thất bại');
    }

    public function destroy(Campaign $campaign, Request $request)
    {

    }
}
