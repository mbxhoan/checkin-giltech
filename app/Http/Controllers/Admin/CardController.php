<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CardDataTable;
use App\DataTables\Admin\ClientForCardDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cards\ListRequest;
use App\Http\Requests\Admin\CardsRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Event;
use App\Models\Card;
use App\Models\CardDetail;
use App\Models\Client;
use App\Services\Admin\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use ZipArchive;

class CardController extends Controller
{
    public function __construct(CardService $service)
    {
        $this->service = $service;
    }

    public function index(ListRequest $request)
    {
        $dataTable = new CardDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();

        return $dataTable->render('admin.cards.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
        ]);
    }

    public function selectEventToCreate(SelectEventToCreateRequest $request)
    {
        return redirect()->route('admin.cards.create', [
            'event' => $request->event_id
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(Card $card)
    {
        $this->authorize('edit', $card);

        $customFieldTemplates = $this->service->custom_field_template()->getListByAttributes([
            'event_id'      => $card->event->id,
            // 'is_lp'         => true,
        ], [], [], 0, [
            'order'         => 'ASC',
        ]);

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $card->event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $card->event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $dataTable = new ClientForCardDataTable($card->event, $card, array_filter([
            "type" => $card->client_type
        ]));

        $totalClients = $dataTable->getFilter();
        $generatedFileCount = $this->service->getGenerateFilesCount($card);

        return $dataTable->render('admin.cards.detail', [
            'cards'                 => $this->service->getListByAttributes([
                    'event_id'      => $card->event->id
                ], [], [], 0, []),
            'cardDetails'           => $card->card_details,
            'cardDetail'            => $this->service->card_detail()->init(),
            'model'                 => $card,
            'mainBg'                => !empty($card->backgroundUrl) ? $card->backgroundUrl->getUrl() : null,
            'event'                 => $card->event,
            'client'                => $this->service->client()->init(),
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            // 'customFieldTemplates'  => $event->getCustomFieldTemplates(true, true),
            'customFieldTemplates'  => $customFieldTemplates,
            'cfTemplatesArray'      => $customFieldTemplates->mapWithKeys(function ($customFieldTemplate) {
                return [$customFieldTemplate->name  => "{$customFieldTemplate->name} - {$customFieldTemplate->description}"];
            })->toArray(),
            'types'                 => $types,
            'fonts'                 => collect(CardDetail::getFonts())
                ->mapWithKeys(fn($item, $key) => [$key => $item['text']])
                ->toArray(),
            'totalClients'          => $totalClients,
            'generatedClients'      => $this->service->getGenerateFilesCount($card),
            // (clone $totalClients)->where('document_pdf', '!=', null),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        $this->authorize('create_card', $event);

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
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        return view('admin.cards.detail', [
            'cards'                 => $this->service->getListByAttributes([
                    'event_id'      => $event->id
                ], [], [], 0, []),
            'model'                 => $this->service->init(),
            'mainBg'                => null,
            'event'                 => $event,
            'types'                 => $types,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CardsRequest $request): RedirectResponse
    {
        $attributes = $request->only([
            'event_id',
            'event_code',
            'client_type',
            'file_name_template',
            'code',
            'background',
            'extension',
            'type',
            'status',
        ]);

        $attributes['status'] = Card::STATUS_NEW;
        $card = $this->service->create($attributes);
        $medias = $request->only(array_keys($card->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($request->hasFile($key) && $request->file($key)->isValid()) {
                    if ($media) {
                        $this->service->attributes['image'] = $media;
                        $this->service->attributes['name'] = $media->getClientOriginalName();

                        if ($result = $this->service->mediaLibraryService()->store()) {
                            if (!empty($result['media'])) {
                                $this->service->update($card->id, [
                                    $key => $result['media']->id
                                ]);
                            } else {
                                return redirect()->route('admin.cards.edit', [
                                    'card'          => $card,
                                ])->withErrors($result['msg']);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.cards.edit', [
            'card'          => $card,
        ])->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CardsRequest $request, Card $card): RedirectResponse
    {
        $attributes = $request->only([
            'event_id',
            'event_code',
            'client_type',
            'file_name_template',
            'code',
            'background',
            'extension',
            'type',
            'status',
        ]);

        $attributes['status'] = Card::STATUS_EDIT;
        $this->service->update($card->id, $attributes);
        $medias = $request->only(array_keys($card->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($request->hasFile($key) && $request->file($key)->isValid()) {
                    if ($media) {
                        $this->service->attributes['image'] = $media;
                        $this->service->attributes['name'] = $media->getClientOriginalName();

                        if ($result = $this->service->mediaLibraryService()->store()) {
                            if (!empty($result['media'])) {
                                $this->service->update($card->id, [
                                    $key => $result['media']->id
                                ]);

                                if ($card->$key) {
                                    $this->service->mediaLibraryService()->deleteMedia($card->$key);
                                }
                            } else {
                                return redirect()->route('admin.cards.edit', [
                                    'card'          => $card,
                                ])->withErrors($result['msg']);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.cards.edit', [
            'card'          => $card,
        ])->withSuccess("Cập nhật thành công");
    }

    public function renderBackground(Event $event, Card $card)
    {
        $this->authorize('render_background_card', $event);

        return $this->responseSuccess([
            'html' => view('admin.cards._background', [
                'card'                  => $card,
                'event'                 => $event,
                'cardDetails'           => $card->card_details->where('status', '!=', CardDetail::STATUS_DELETED),
                'cardDetail'            => $this->service->card_detail()->init(),
                'mainBg'                => !empty($card->backgroundUrl) ? $card->backgroundUrl->getUrl() : null,
                'width'                 => !empty($card->backgroundUrl) ? (Image::make($card->backgroundUrl->getPath()))->width() : null,
                'height'                => !empty($card->backgroundUrl) ? (Image::make($card->backgroundUrl->getPath()))->height() : null,
            ])->render()
        ]);
    }

    public function generate(Request $request, Card $card)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:OK'],
        ]);

        $this->service->generate($card->id);
        return redirect()->route('admin.cards.edit', [
            'card'          => $card,
        ])->withSuccess("Các thiệp/thư mời đang được tạo");
    }

    public function getProgress(Card $card)
    {
        $this->authorize('view_progress', $card);

        $clients = $this->service->client()->getListByAttributes(array_filter([
            'event_id'  => $card->event_id,
            'type'      => $card->client_type
        ]));

        return $this->responseSuccess([
            'html'          => view('components._progress', [
                'total'     => $clients->count(),
                // 'completed' => $clients->where('document_pdf', '!=', null)->count(),
                'completed' => $this->service->getGenerateFilesCount($card),
                'dataTime'  => 3,
                'dataEle'   => '#progress',
                'dataUrl'   => route('admin.cards.progress', $card),
            ])->render()
        ]);
    }

    public function downloadCardImages(Card $card)
    {
        $this->authorize('download_images', $card);

        $clients = $this->service->client()->getListByAttributes(array_filter([
            'event_id'  => $card->event_id,
            'type'      => $card->client_type
        ]));

        $count = $clients->count();
        $zipFileName = "{$card->code}_{$count}_thiep_".now()->timestamp.".zip";
        $zipPath = storage_path("app/public/img/{$zipFileName}");

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
                if ($client->document_pdf) {
                    $path = "public/{$client->document_pdf}";

                    if (Storage::exists($path)) {
                        $bgPath = !empty($card->backgroundUrl) ? "public/medias/{$card->backgroundUrl->getPathRelativeToRoot()}" : null;
                        $fileName = $this->service->getFileNameByCardTemplate($card, $client);
                        $extension = pathinfo($bgPath, PATHINFO_EXTENSION);
                        $fileName = basename($client->document_pdf);
                        $zip->addFile(storage_path("app/{$path}"), "{$client->type}/{$fileName}");
                    }
                }
            }

            $zip->close();
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            Log::error($th);
            if (auth()->user()->isSysAdmin()) {
                return back()->withErrors("Đã có lỗi xảy ra trong quá trình tải thiệp/thư mời: {$th->getMessage()}");
            }
        }

        return back()->withErrors("Đã có lỗi xảy ra trong quá trình tải thiệp/thư mời");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getFullScreen(Card $card)
    {
        $this->authorize('get_full_screen', $card);

        $customFieldTemplates = $this->service->custom_field_template()->getListByAttributes([
            'event_id'      => $card->event->id,
            // 'is_lp'         => true,
        ], [], [], 0, [
            'order'         => 'ASC',
        ]);

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $card->event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $card->event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $dataTable = new ClientForCardDataTable($card->event, $card, array_filter([
            "type" => $card->client_type
        ]));

        $totalClients = $dataTable->getFilter();

        return $dataTable->render('admin.cards._aim', [
        // return view('admin.cards._aim', [
            'model'                 => $card,
            'cardDetail'            => $this->service->card_detail()->init(),
            'client'                => $this->service->client()->init(),
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $customFieldTemplates,
            'cfTemplatesArray'      => $customFieldTemplates->mapWithKeys(function ($customFieldTemplate) {
                return [$customFieldTemplate->name  => "{$customFieldTemplate->name} - {$customFieldTemplate->description}"];
            })->toArray(),
            'fonts'                 => collect(CardDetail::getFonts())
                ->mapWithKeys(fn($item, $key) => [$key => $item['text']])
                ->toArray(),
            'types'                 => $types,
            'fonts'                 => collect(CardDetail::getFonts())
                ->mapWithKeys(fn($item, $key) => [$key => $item['text']])
                ->toArray(),
            'totalClients'          => $totalClients,
            'generatedClients'      => $this->service->getGenerateFilesCount($card),
        ]);
    }
}
