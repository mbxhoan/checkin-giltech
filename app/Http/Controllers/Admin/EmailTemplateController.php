<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplates\ListRequest;
use App\Http\Requests\Admin\EmailTemplates\SendTestPostmarkRequest;
use App\Http\Requests\Admin\EmailTemplates\TemplatePostmarkRequest;
use App\Services\Admin\EmailTemplateService;

class EmailTemplateController extends Controller
{
    public function __construct(EmailTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application products index.
     */
    public function index(ListRequest $request)
    {
        $result = $this->service->getPostmarkTemplates();
        return view('admin.email_templates.index', [
            'templates'         => $result['Templates'] ?? [],
            'total'             => $result['TotalCount'] ?? 0,
        ]);
    }

    public function editPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId);

        if (count($result)) {
            return view('admin.email_templates.detail', [
                'object'            => $result,
            ]);
        }

        abort(404);
    }

    public function syncPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId, true);

        if (count($result)) {
            return view('admin.email_templates.detail', [
                'object'            => $result,
            ]);
        }

        abort(404);
    }

    public function viewPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId);
        return isset($result['FullHtmlBody']) ? response($result['FullHtmlBody']) : abort(404);

        return view('admin.email_templates.index', [
            'templates'         => $result['Templates'],
            'total'             => $result['TotalCount'],
        ]);
    }

    public function updatePostmarkTemplate(TemplatePostmarkRequest $request, int $templateId)
    {
        $attributes = $request->all();
        $result = $this->service->middleware_email_template()->updatePostmarkTemplate($templateId, $attributes);

        if (count($result) && isset($result['Name'])) {
            return back()->withSuccess("Cập nhật thành công");
        }

        return back()->withErrors("Cập nhật KHÔNG thành công");
    }

    public function sendTestPostmarkTemplate(SendTestPostmarkRequest $request, int $templateId)
    {
        $attributes = $request->all();
        $result = $this->service->middleware_email_template()->sendTestPostmarkTemplate($templateId, $attributes);

        if (count($result) && (isset($result['Message']) && $result['Message'] == "OK")) {
            return back()->withSuccess("Đã gửi thành công");
        }

        return back()->withErrors("Đã có lỗi xảy ra");
    }

    public function reSyncPostmarkTemplates(ListRequest $request)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplates(true);
        return view('admin.email_templates.index', [
            'templates'         => $result['Templates'] ?? [],
            'total'             => $result['TotalCount'] ?? 0,
        ]);
    }
}
