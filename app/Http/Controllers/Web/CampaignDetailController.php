<?php

namespace App\Http\Controllers\Web;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CampaignDetail;
use App\Services\Web\CampaignDetailService;

class CampaignDetailController extends Controller
{
    public function __construct(CampaignDetailService $service)
    {
        $this->service = $service;
    }

    public function viewEmail(CampaignDetail $campaign_detail)
    {
        $params = [
            'name'          => $campaign_detail->name,
            'email'         => $campaign_detail->email,
            'phone'         => $campaign_detail->phone,
            'qrcode'        => $campaign_detail->qrcode,
            'img_qrcode'    => $campaign_detail->img_qrcode,
            'document_pdf'  => $campaign_detail->document_pdf
        ];

        $params = array_merge($params, (array)json_decode($campaign_detail->custom_fields));
        $templateId = $campaign_detail->campaign->template_id;
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId);
        $html = $result['FullHtmlBody'] ?? null;

        if ($html) {
            $html = Helper::fillTemplatePlaceholders($html, $params);
            return response($html);
        }

        abort(404, 'Template not found');
    }
}
