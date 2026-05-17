<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignDetail;
use Illuminate\Http\Request;
use App\Services\Admin\CampaignDetailService;

class CampaignDetailController extends Controller
{
    public function __construct(CampaignDetailService $service)
    {
        $this->service = $service;
    }

    public function viewEmail(CampaignDetail $campaign_detail)
    {

    }

    public function sendMail(Request $request, Campaign $campaign)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:SEND'],
        ]);

        if ($this->service->setupEmails($campaign)) {
            return redirect()->route('admin.campaigns.edit', $campaign)
                ->withSuccess('Email đang được gửi đi thành công');
        }

        return back()->withErrors('Đã có lỗi xảy ra trong quá trình setup');
    }

    public function updateField(Request $request)
    {
        $request->validate([
            'id'      => 'required|integer|exists:campaign_details,id',
            // 'field'      => 'required|integer|exists:campaign_details,id',
        ]);

        $model = $this->service->findByAttributes([
            'id' => $request->id
        ]);

        if ($model) {
            $this->service->update($model->id, [
                $request->field => $request->value
            ]);

            return $this->responseSuccess();
        }

        return $this->responseError();
    }
}
