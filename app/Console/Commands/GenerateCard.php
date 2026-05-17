<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Card;
use App\Models\CardDetail;
use App\Services\Admin\CardService;
use App\Services\Admin\ClientService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Exception;
use Intervention\Image\ImageManager;
use Normalizer;

class GenerateCard extends Command
{
    private const CLIENT_CHUNK_SIZE = 25;
    private const DEFAULT_IMAGE_QUALITY = 82;

    private $options;
    private $modelCard;
    private $clientType;
    private ?ImageManager $imageManager = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'generate:cards {cardId} {--clientId=} {--isTest=} {--emptyOnly=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Cards';

    /**
     * Execute the console command.
     *
     *
     * @return int
     */

    public function __construct()
    {
        parent::__construct();
    }

    public function card()
    {
        return new CardService();
    }

    public function client()
    {
        return new ClientService();
    }

    public function handle()
    {
        $result = [];
        $cardId = !empty($this->argument('cardId')) ? $this->argument('cardId') : '';
        $this->options = (object)$this->options();

        /* START */
        $start = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");
        $this->modelCard = $this->card()->findById($cardId);

        if (!empty($this->modelCard)) {
            $this->modelCard->loadMissing([
                'card_details' => function ($query) {
                    $query->orderBy('id');
                },
                'event.custom_field_templates',
                'backgroundUrl',
            ]);
        }

        if (!empty($this->modelCard)) {
            try {
                $selectColumns = [
                    'id',
                    'event_id',
                    'event_code',
                    'qrcode',
                    'img_qrcode',
                    'document_pdf',
                    'card_link_mobile',
                    'card_link_desktop',
                    'name',
                    'email',
                    'avatar',
                    'type',
                    'custom_fields',
                    'status',
                ];

                if (Schema::hasColumn('clients', 'phone')) {
                    $selectColumns[] = 'phone';
                }

                $clients = $this->client()->getQuery()
                    ->select($selectColumns)
                    ->with(['event.custom_field_templates', 'avatarUrl'])
                    ->where(array_filter([
                        'event_id'  => $this->modelCard->event_id,
                        'type'      => $this->modelCard->client_type
                    ]));

                if (!empty($this->options->emptyOnly) && $this->options->emptyOnly) {
                    $clients->whereNull('document_pdf');
                }

                $result = $this->generate($clients);
            } catch (Exception $e) {
                Log::alert($e);
                Log::info("Error occurred on generating cards #{$this->modelCard->id} - {$this->modelCard->code}");
                $this->error("Executed with error(s). {$e->getMessage()}");
            }
        } else {
            $this->error('NOT FOUND CARD');
        }

        /* END */

        $end = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");
        $this->comment("Finished at {$end}");
        $diff = (strtotime($end) - strtotime($start))/60;
        $this->comment("Collapsed time: {$diff} minutes !");
        $this->question('COMPLETED');
        return $result;
    }

    public function resetCards($clients)
    {
        foreach ($clients as $client) {
            // $client->update([
            //     'card_link_mobile'  => null,
            //     'card_link_desktop' => null,
            //     'document_pdf'      => null
            // ]);
        }

        return true;
    }

    public function generate($clients)
    {
        $result = [];
        $this->clientType = !empty($this->modelCard->client_type) ? $this->modelCard->client_type : $this->modelCard->event_code;
        // Remove characters that may mess up the folder path
        $this->clientType = preg_replace('/[\/\\\\\.,]+/', '_', $this->clientType);

        // $this->clientType = "{$this->clientType}/{$this->modelCard->device}";
        $savePath = "public/img/{$this->modelCard->event_code}/{$this->clientType}";

        if (!Storage::disk('local')->exists($savePath)) {
            Storage::makeDirectory($savePath);
        }

        $bgPath = !empty($this->modelCard->background) ? "public/medias/{$this->modelCard->backgroundUrl->getPathRelativeToRoot()}" : null;
        $extension = pathinfo($bgPath, PATHINFO_EXTENSION);
        // $extension = $this->modelCard->extension;

        // $bar = $this->output->createProgressBar($clients->count());

        if (empty($bgPath) || !Storage::disk('local')->exists($bgPath)) {
            $this->error('Not found background images');
            return false;
        }

        $bgAbsolutePath = Storage::disk('local')->path($bgPath);
        if (!$this->validatePngFileIfNeeded($bgAbsolutePath)) {
            $this->error('Background image is invalid or corrupted');
            return false;
        }

        if ($clients instanceof Builder) {
            $clientCount = (clone $clients)->count();
            $this->line("Generating {$clientCount} clients");

            $generated = 0;
            $clients->chunkById(self::CLIENT_CHUNK_SIZE, function ($chunk) use (&$generated, $bgPath, $savePath, $extension) {
                foreach ($chunk as $client) {
                    if (!empty($this->options->isTest) && $this->options->isTest == "true" && $generated >= 1) {
                        return false;
                    }

                    $generated++;
                    $fileName = Helper::removeSpecialCharacters($client->qrcode);

                    if (!empty($this->modelCard->file_name_template)) {
                        $fileName = $this->card()->getFileNameByCardTemplate($this->modelCard, $client);
                    }

                    $result = $this->generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension);
                    $this->info("{$generated}. Inserted {$client->name} - {$fileName}.{$extension}");
                    $this->releaseImageResources();
                }
            });

            $this->info("INSERT CARD => SUCCESS");
            return $result;
        }

        if (!empty($this->options->clientId) && is_numeric($this->options->clientId)){
            $clientId = $this->options->clientId;
            $client = $this->client()->findById($clientId);

            if (!empty($client)) {
                $fileName = Helper::removeSpecialCharacters($client->qrcode);

                if (!empty($this->modelCard->file_name_template)) {
                    $fileName = $this->card()->getFileNameByCardTemplate($this->modelCard, $client);
                }

                $result = $this->generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension);
                $this->info("Inserted {$client->name} - {$fileName}.{$extension}");
            }
        } else {
            $this->line("Generating {$clients->count()} clients");
            foreach ($clients as $key => $client) {
                if (!empty($this->options->isTest) && $this->options->isTest == "true") {
                    if (($key) == 1) {
                        break;
                    }
                }

                $fileName = Helper::removeSpecialCharacters($client->qrcode);

                if (!empty($this->modelCard->file_name_template)) {
                    $fileName = $this->card()->getFileNameByCardTemplate($this->modelCard, $client);
                }

                $result = $this->generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension);
                $this->info(++$key . ". Inserted {$client->name} - {$fileName}.{$extension}");
                $this->releaseImageResources();
            }
        }

        $this->info("INSERT CARD => SUCCESS");
        return $result;
    }

    public function generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension)
    {
        $bgAbsolutePath = Storage::disk('local')->path($bgPath);
        $bg = $this->image()->make($bgAbsolutePath);
        $originalWidth = max(1, $bg->width());
        $scaleRatio = 1.0;
        $scaledWidth = (int) ($this->modelCard->scaled ?? 0);

        if ($scaledWidth > 0 && $originalWidth > $scaledWidth) {
            $bg->resize($scaledWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $scaleRatio = $bg->width() / $originalWidth;
        }

        foreach ($this->modelCard->card_details as $cardDetail) {
            if ($cardDetail->status != CardDetail::STATUS_DELETED) {
                switch ($cardDetail->type) {
                    case CardDetail::TYPE_NONE:
                        break;
                    case CardDetail::TYPE_QRCODE:
                        if (!empty($client->img_qrcode)) {
                            $qrcodePath = "public/{$client->img_qrcode}";

                            if (!Storage::exists($qrcodePath)) {
                                $this->error('Not found qrcode images');
                                break;
                            }

                            $bg = $this->addQrcode($bg, $qrcodePath, $cardDetail, $scaleRatio);
                        }
                        break;
                    case CardDetail::TYPE_IMG:
                        if (in_array($cardDetail->field, [
                            "qrcode",
                        ])) {
                            if (!empty($client->img_qrcode)) {
                                $qrcodePath = "public/{$client->img_qrcode}";

                                if (!Storage::exists($qrcodePath)) {
                                    $this->error('Not found qrcode images');
                                    break;
                                }

                                $bg = $this->addQrcode($bg, $qrcodePath, $cardDetail, $scaleRatio);
                            }
                        }
                        break;
                    case CardDetail::TYPE_FIELD:
                        $field = $cardDetail->field;

                        if (in_array($field, ['name', 'email', 'phone'])) {
                            $value = $this->resolveClientFieldValue($client, $field);

                            if ($field == "name" && $value == "UNNAMED") {
                                $value = "";
                            }
                        } else {
                            $customFields = $client->getCustomFieldValues();
                            $value = isset($customFields[$field]) ? $customFields[$field] : "";
                        }

                        $value = trim($value);
                        $value = Normalizer::normalize($value, Normalizer::FORM_C);

                        /* customize */
                        /* hidec-2025 */
                        // if ($client->event_code == "hidec-2025" && $field == "tickets") {
                        //     $values = explode(', ', $value);
                        //     foreach ($values as $val) {
                        //         $bg = $this->addText($bg, $val, $cardDetail);
                        //         $cardDetail->pos_y += 1.5;
                        //     }

                        //     continue;
                        // } else {

                        // }

                        $bg = $this->addText($bg, $value, $cardDetail);
                        /* end */
                        break;
                    default:
                        $value = $cardDetail->text;
                        $value = trim($value);
                        $value = Normalizer::normalize($value, Normalizer::FORM_C);
                        $bg = $this->addText($bg, $value, $cardDetail);
                }
            }
        }

        $documentPdfPath = "img/{$this->modelCard->event_code}/{$this->clientType}/{$fileName}.{$extension}";

        /* UPDATE INTO CLIENTS */
        $client->update([
            'card_link_mobile'  => $documentPdfPath,
            'card_link_desktop' => $documentPdfPath,
            'document_pdf'      => $documentPdfPath
        ]);

        if (!Storage::disk('local')->exists("{$savePath}")) {
            Storage::makeDirectory("{$savePath}");
        }

        if (method_exists($bg, 'interlace')) {
            $bg->interlace(false);
        }
        $bg->save(Storage::disk('local')->path("{$savePath}/{$fileName}.{$extension}"), self::DEFAULT_IMAGE_QUALITY);
        $this->releaseImageResources($bg);

        /* test */

        return [
            'document_pdf' => $documentPdfPath
        ];
    }

    private function resolveClientFieldValue($client, string $field)
    {
        if ($field !== 'phone') {
            return $client->$field ?? null;
        }

        return $client->getAttribute('phone') ?? ($client->custom_fields['phone'] ?? null);
    }

    public function addText($image, $text, $modelCardDetail)
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        $fontSize = ($modelCardDetail->font_size/100)*$image->height();
        $posX = ($modelCardDetail->pos_x/100)*$image->width();
        $posY = ($modelCardDetail->pos_y/100)*$image->height();
        $fontPath = $modelCardDetail->getFont($modelCardDetail->font)['path'] ?? $modelCardDetail->getFont("roboto");
        // var_dump($fontPath);
        // var_dump(public_path("assets/fonts/{$fontPath}"));
        // var_dump($posX);
        // var_dump($posY);

        // var_dump($image->width());
        // var_dump($image->height());
        // var_dump("text: { $text}");
        // var_dump("font size: {$fontSize}");
        // var_dump("pos x: {$posX}");
        // var_dump("pos Y: {$posY}");

        $image->text($text, $posX, $posY, function ($font) use ($modelCardDetail, $fontSize, $fontPath) {
            $font->file(public_path("assets/fonts/{$fontPath}"));
            $font->size($fontSize);
            $font->color($modelCardDetail->color);
            $font->align($modelCardDetail->h_align);
            $font->valign("center");
            $font->angle($modelCardDetail->rotate);
        });

        return $image;
    }

    public function addQrcode($background, $imagePath, $modelCardDetail, float $scaleRatio = 1.0)
    {
        $posX = round(($modelCardDetail->pos_x/100)*$background->width());
        $posY = round(($modelCardDetail->pos_y/100)*$background->height());
        $width = max(1, (int) round(($modelCardDetail->width ?? 0) * $scaleRatio));
        $height = max(1, (int) round(($modelCardDetail->height ?? 0) * $scaleRatio));

        $absolutePath = Storage::disk('local')->path($imagePath);
        if (!$this->validatePngFileIfNeeded($absolutePath)) {
            $this->error("Invalid or corrupted PNG: {$imagePath}");
            return $background;
        }

        $image = $this->image()->make($absolutePath)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $background->insert(
            $image,
            $modelCardDetail->h_align.'-'.$modelCardDetail->v_align,
            $posX,
            $posY
        );

        return $background;
    }

    private function image(): ImageManager
    {
        if ($this->imageManager) {
            return $this->imageManager;
        }

        $driver = config('image.driver', 'gd');
        if (!in_array($driver, ['gd', 'imagick'], true)) {
            $driver = 'gd';
        }
        $this->imageManager = new ImageManager(['driver' => $driver]);
        return $this->imageManager;
    }

    private function validatePngFileIfNeeded(string $absolutePath): bool
    {
        if (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) !== 'png') {
            return true;
        }

        $pngInfo = $this->readPngHeader($absolutePath);
        if (!$pngInfo) {
            return false;
        }

        if (!empty($pngInfo['interlaced'])) {
            Log::info("Interlaced PNG detected: {$absolutePath}");
        }

        return true;
    }

    private function releaseImageResources(?object $image = null): void
    {
        if ($image && method_exists($image, 'destroy')) {
            $image->destroy();
        }

        unset($image);
        gc_collect_cycles();
    }

    private function readPngHeader(string $absolutePath): ?array
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $fh = @fopen($absolutePath, 'rb');
        if (!$fh) {
            return null;
        }

        $sig = fread($fh, 8);
        if ($sig !== "\x89PNG\r\n\x1a\n") {
            fclose($fh);
            return null;
        }

        $lenData = fread($fh, 4);
        $type = fread($fh, 4);
        if (strlen($lenData) !== 4 || strlen($type) !== 4) {
            fclose($fh);
            return null;
        }

        $len = unpack('N', $lenData)[1] ?? null;
        if ($type !== 'IHDR' || !is_int($len) || $len < 13) {
            fclose($fh);
            return null;
        }

        $ihdr = fread($fh, 13);
        fclose($fh);

        if (strlen($ihdr) !== 13) {
            return null;
        }

        $fields = unpack('Nwidth/Nheight/CbitDepth/CcolorType/Ccompression/Cfilter/Cinterlace', $ihdr);
        if (!is_array($fields) || !isset($fields['interlace'])) {
            return null;
        }

        return [
            'width' => $fields['width'] ?? null,
            'height' => $fields['height'] ?? null,
            'interlaced' => ($fields['interlace'] ?? 0) === 1,
        ];
    }

    // private function getFileNameByCardTemplate($client)
    // {
    //     $datas = $client->getFullFields();
    //     unset($datas['avatar']);
    //     unset($datas['img_qrcode']);
    //     return Helper::generateQrcodeByTemplate($this->modelCard->file_name_template, $datas);
    // }
}
