<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\Web\Clients\ListRequest;
use App\Http\Requests\Web\Clients\StoreRequest;
use App\Models\Client;
use App\Services\Web\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class ClientController extends Controller
{
    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    /**
     * Generate the img qrcode if not exist
     */
    public function generateQrcodeById($id)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            $file = $client->img_qrcode;

            /* có hình mã qrcode */
            if ($file) {
                $filePath = "public/{$file}";

                if ($file && Storage::exists($filePath)) {
                    return response()->file(storage_path("app/{$filePath}"));
                }
            }

            /* chưa có hình mã qrcode */
            $this->service->middleware_client()->generateQrcode($client->event_code, $client->qrcode);

            $client = $this->service->findByAttributes([
                'id' => $id
            ]);

            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
            /* chưa có nữa thì thua. Lỗi */
            /* end */
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Show the img qrcode
     */
    public function viewQrcode($qrcode)
    {
        $client = $this->service->findByAttributes([
            'qrcode' => $qrcode
        ]);

        if ($client) {
            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Show the img qrcode
     */
    public function viewQrcodeById($id)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
        }

        return redirect()->route('web.home')
            ->withErrors('Không tìm thấy thông tin');

        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, string $slug): RedirectResponse
    {
        $client = null;
        $this->service->attributes = array_filter($request->only([
            'id',
            'qrcode',
            'event_id',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'campaign_id',
            'ref_id'
        ]));

        $event = $this->service->event()->findByAttributes([
            'id' => $this->service->attributes['event_id'],
        ]);

        if (!$event) {
            return back()->with('error', "Không tìm thấy sự kiện");
        }

        if (isset($this->service->attributes['id'])) {
            $id = $this->service->attributes['id'];
            $client = $this->service->findByAttributes([
                'id'    => $id,
            ]);
        }

        $customFields = $this->service->attributes['custom_fields'] ?? [];
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['register_source'] = Client::REGISTER_LP;

        if (!isset($this->service->attributes['qrcode'])) {
            $this->service->attributes['qrcode'] = $event->generateQrcodeOnSetting($event->code, $customFields['phone'] ?? null, $this->service->attributes['email'] ?? null, $this->service->attributes['name'], $customFields ?? []);
        }

        if ($client) {
            /* update */
            unset($this->service->attributes['id']);
            unset($this->service->attributes['qrcode']);
            $this->service->update($client->id, $this->service->attributes);
            $client->refresh();
        } else {
            /* create */
            $client = $this->service->create($this->service->attributes);
        }

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        /* register */
        $result = $this->service->register($client);

        $landingPage = $this->service->landing_page()->findByAttributes([
            'slug' => $slug,
        ]);

        if ($landingPage) {
            return redirect()->route('landing_pages.success', [
                'slug'      => $landingPage->slug,
                'qrcode'    => $client->qrcode
            ])->with('success', $result['msg']);
        }

        return redirect()->route('web.home')->with('success', "Tạo mới thành công");
    }

    public function viewCard(int $cardId, string $clientId)
    {
        $card = $this->service->card()->findByAttributes([
            'id' => $cardId
        ]);

        if (!$card) {
            return response()->json(['error' => 'Card not found.'], 404);
        }

        $client = $this->service->findByAttributes([
            'id' => $clientId
        ]);

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        if (!empty($client->document_pdf) && Storage::exists("public/{$client->document_pdf}")) {
            $filePath = "public/{$client->document_pdf}";
            return response()->file(storage_path("app/{$filePath}"));
        }

        $result = $this->service->middleware_card()->generateCardNow($cardId, $clientId);

        if ($result['status']) {
            $client->refresh();
            if (!empty($client->document_pdf) && Storage::exists("public/{$client->document_pdf}")) {
                $filePath = "public/{$client->document_pdf}";
                return response()->file(storage_path("app/{$filePath}"));
            } else {
                abort(404);
                return response()->json(['error' => 'Không tìm thấy file. Vui lòng thử lại sau...'], 404);
            }
        } else {
            abort(404, $result['msg']);
            return response()->json([
                'status'            => 'error',
                'status_code'       => 400,
                'message'           => $result['msg'],
            ]);
        }

        abort(404);
    }

    public function viewDocumentPdf(int $clientId)
    {
        $client = $this->service->findByAttributes([
            'id' => $clientId
        ]);

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        /* check xem có card chưa */
        $file = $client->document_pdf;
        $filePath = "public/{$file}";

        /* comment đoạn này là cứ load trang sẽ chạy card mới */
        if ($file && Storage::exists($filePath)) {
            // return response()->file(storage_path("app/{$filePath}"));

            $fullPath = storage_path("app/{$filePath}");
            $fileName = pathinfo($fullPath, PATHINFO_FILENAME);
            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

            $headers = [
                'Content-Type'          => Storage::mimeType($fullPath),
                'Content-Disposition'   => "inline; filename=\"{$fileName}.{$extension}\"",
            ];

            return new StreamedResponse(function () use ($fullPath) {
                $stream = fopen($fullPath, 'r');

                while (!feof($stream)) {
                    echo fread($stream, 1024);
                }

                fclose($stream);
            }, 200, $headers);
        }

        abort(404);
    }

    public function streamDocumentPdf(int $clientId)
    {
        $client = $this->service->findByAttributes(['id' => $clientId]);
        if (!$client) {
            abort(404);
        }

        $filePath = $client->file_path;

        // 1) Guard against null/empty paths
        if (empty($filePath) || !is_string($filePath)) {
            abort(404, 'File path is empty.');
        }

        // 2) Normalize path (remove leading slashes; strip accidental "public/" prefix)
        $filePath = ltrim($filePath, '/');
        if (str_starts_with($filePath, 'public/')) {
            $filePath = substr($filePath, strlen('public/'));
        }

        // 3) Use the correct disk
        $disk = Storage::disk('public');

        // 4) Check existence on the disk (NOT absolute path)
        if (!$disk->exists($filePath)) {
            abort(404, 'File not found.');
        }

        // 5) Build headers using disk metadata
        $mime = $disk->mimeType($filePath) ?? 'application/pdf';
        $filename = pathinfo($filePath, PATHINFO_FILENAME) ?: 'document';
        $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'pdf';

        $headers = [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '.' . $extension . '"',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // 6) Stream from the disk (works for local and S3)
        return new StreamedResponse(function () use ($disk, $filePath) {
            $stream = $disk->readStream($filePath);
            if ($stream === false) {
                // Couldn’t open stream for some reason
                return;
            }
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    public function viewQrcodes(string $eventCode, string $qrcode)
    {
        /* customize */
        /* tba-event-1110 */
        if ($eventCode != 'tran-dia-da-vi') {
            return redirect()->route('web.home')
                ->withErrors('Không tìm thấy thông tin sự kiện');
        }

        $client = Client::where([
            'event_code'    => "tba-event-1110",
            'qrcode'        => $qrcode,
        ])
            ->where('status', '!=', Client::STATUS_DELETED)
            ->first();

        if ($client) {
            $clients = Client::where([
                'event_code'    => "tba-event-1110",
                'type'          => $client->type,
            ])
                ->where('qrcode', '!=', $qrcode)
                ->where('status', '!=', Client::STATUS_DELETED)
                ->get();

            if ($clients) {
                return view('web.qrcodes', [
                    'client'    => $client,
                    'clients'   => $clients,
                ]);
            }
        }

        return redirect()->route('web.home')
            ->withErrors('Không tìm thấy thông tin');
    }

    public function downloadQrcodeImages(string $eventCode, Request $request)
    {
        /* customize */
        /* tba-event-1110 */
        if ($eventCode != 'tba-event-1110') {
            return back()->withErrors('Không tìm thấy thông tin sự kiện');
        }

        $query = $this->service->getQuery()
            ->where('event_code', $eventCode)
            ->where('status', '!=', Client::STATUS_DELETED);

        // $query = $this->service->applyFilters($query);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->except) {
            $query->where('qrcode', '!=', $request->except);
        }

        $count = $query->count();
        $clients = $query->get();
        $zipFileName = ($request->type ? $request->type : $eventCode). "_{$count}_qrcodes_".now()->timestamp.".zip";
        $zipPath = storage_path("app/public/qrcodes/{$zipFileName}");

        if (!Storage::disk('public')->exists('tmp')) {
            Storage::disk('public')->makeDirectory('tmp');
        }

        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return back()->withErrors([
                    'Không thể tạo file ZIP.'
                ]);
            }

            foreach ($clients as $client) {
                if ($client->img_qrcode) {
                    $path = "public/{$client->img_qrcode}";
                    if (Storage::exists($path)) {
                        $zip->addFile(storage_path("app/{$path}"), basename($path));
                    } else {
                        Artisan::call("generate:image-qrcode {$client->event_code} --qrcode={$client->qrcode}");
                        $zip->addFile(storage_path("app/{$path}"), basename($path));
                    }
                }
            }

            $zip->close();
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            Log::error($th);
            return back()->withErrors("Đã có lỗi xảy ra trong quá trình tạo và tải qrcodes {$th->getMessage()}");
        }

        return back()->withErrors("Đã có lỗi xảy ra trong quá trình tạo và tải qrcodes");
    }

    /* customize */
    /* hidec-2025 */
    public function getCert(string $eventCode, string $qrcode)
    {
        if ($eventCode != 'hidec-2025') {
            return back()->withErrors('Không tìm thấy thông tin sự kiện');
        }

        $client = Client::where([
            'event_code'    => "hidec-2025",
            'qrcode'        => $qrcode,
        ])
            ->where('status', '!=', Client::STATUS_DELETED)
            ->first();

        if (!empty($client)) {
            $filename = "file.pdf";
            $path = "public/{$filename}"; // e.g. 'public/example.pdf'

            if (!Storage::exists($path)) {
                abort(404, 'Không tìm thấy chứng chỉ');
            }

            return new StreamedResponse(function () use ($path) {
                $stream = Storage::readStream($path);
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'inline; filename="'.basename($path).'"'
            ]);
        }

        abort(404, 'Không tìm thấy chứng chỉ');
        return redirect()->route('web.home')
            ->withErrors('Không tìm thấy thông tin khách hàng');
    }

    public function micrositePinaco(string $eventCode, string $token)
    {
        $eventCode = strtolower($eventCode);
        $client = Client::where('event_code', $eventCode)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->where('custom_fields->token', $token) 
            ->first();

        if (!$client) {
            abort(404, 'Không tìm thấy thông tin khách mời hoặc thư mời không hợp lệ.');
        }

        return view('web.landing_pages.pinaco.index', [
            'client' => $client 
        ]);
    }

    public function updateAttendancePinaco(Request $request, string $eventCode, string $token)
    {
        $eventCode = strtolower($eventCode);
        $client = Client::where('event_code', $eventCode)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->where('custom_fields->token', $token) 
            ->first();

        if (!$client) {
            return response()->json([
                'success' => false, 
                'message' => 'Không tìm thấy thông tin khách mời.'
            ], 404);
        }

        $customFields = $client->custom_fields ?? [];
        $customFields['attend'] = $request->input('attendance') === 'yes' ? 'yes' : 'no';
        
        $client->custom_fields = $customFields;
        $client->save();

        return response()->json([
            'success' => true, 
            'message' => 'Cập nhật thành công.'
        ]);
    }

}
