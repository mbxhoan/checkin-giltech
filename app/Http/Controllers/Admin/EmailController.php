<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Email\EmailExport;
use App\Exports\Email\ErrorEmailExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emails\ChangeStatusRequest;
use App\Http\Requests\Admin\Emails\ExportReportRequest;
use App\Models\Campaign;
use App\Models\Email;
use App\Models\Event;
use App\Services\Admin\EmailService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmailController extends Controller
{
    public function __construct(EmailService $service)
    {
        $this->service = $service;
    }

    public function changeStatus(Email $email, ChangeStatusRequest $request)
    {
        $status = $request->get('status');

        switch ($status) {
            case Email::STATUS_NEW:
                if ($email->status == Email::STATUS_WAITING) {
                    $this->service->update($email->id, [
                        'status' => Email::STATUS_NEW,
                    ]);
                } else {
                    return $this->responseError("Trạng thái không hợp lệ");
                }
                break;
            case Email::STATUS_WAITING:
                if (in_array($email->status, [
                    Email::STATUS_SENT,
                    Email::STATUS_NEW,
                ])) {
                    $this->service->update($email->id, [
                        'status' => Email::STATUS_WAITING,
                    ]);
                    $email = $this->service->setEmailWaiting($email, "job");
                } else {
                    return $this->responseError("Trạng thái không hợp lệ");
                }
                break;
            case Email::STATUS_SENT:
                if ($email->status == Email::STATUS_WAITING) {
                    $email = $this->service->sendMailNow($email);
                    $sentAt = humanize_date($email->sent_at, 'H:i:s d-m-Y');
                } else {
                    return $this->responseError("Trạng thái không hợp lệ");
                }
                break;
            default:
                return $this->responseError("Trạng thái không hợp lệ");
        }

        $email = $this->service->findById($email->id);

        return $this->responseSuccess([
            'html1'         => view('admin.emails._status', [
                'email'     => $email,
            ])->render(),
            'html2'         => view('admin.emails._btn-status', [
                'email'     => $email,
            ])->render(),
            'sent_at'       => $sentAt ?? null,
        ], "Cập nhật trạng thái thành công");

        return $this->responseError("Không thể cập nhật trạng thái thành công");
    }

    public function cancelByCampaign(Campaign $campaign, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:STOP'],
        ]);

        $emails = $campaign->getEmails([
            Email::STATUS_WAITING,
            // Email::STATUS_SENT,
        ])->get();

        foreach ($emails as $email) {
            $this->service->update($email->id, [
                'status' => Email::STATUS_NEW,
            ]);
        }

        return back()->withSuccess("Đã dừng tiến trình");
    }

    public function exportReport(Event $event, ExportReportRequest $request)
    {
        $this->authorize('export_report_email', $event);

        $file = "public/exports/excels/"."EmailReport_".$event->code."_".date('Ymd_His').'.xlsx';
        $filePath = storage_path("app/{$file}");

        Excel::store(
            new EmailExport(
                $event->code,
                $request->campaign_id ?? 0,
            ),
            $file
        );

        return response()->download($filePath)->deleteFileAfterSend(false);

        // return Excel::download(new EmailExport($request->event_code, $request->campaign_id ?? 0), "EmailReport_".$request->event_code."_".date('Ymd_His').'.xlsx');
    }

    public function exportErrorEmail(Campaign $campaign)
    {
        $this->authorize('export_report_error_email', $campaign);

        return Excel::download(new ErrorEmailExport($campaign->id), "ErrorEmail_{$campaign->id}_".date('Ymd_His').'.xlsx');
    }
}
