<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Card;
use App\Models\CardDetail;
use App\Services\Admin\CardService;
use App\Services\Admin\ClientService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Image;
use Exception;
use Normalizer;

class GenerateCardBK extends Command
{
    private $options;
    private $modelCard;
    private $clientType;

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
            try {
                $clients = $this->client()->getListByAttributes(array_filter([
                    'event_id'  => $this->modelCard->event_id,
                    'type'      => $this->modelCard->client_type
                ]));

                if (!empty($this->options->emptyOnly) && $this->options->emptyOnly) {
                    $clients = $clients->where([
                        'document_pdf', '=', null
                    ]);
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
            /* reset cột */
            $this->resetCards($clients);

            $this->line("Generating {$clients->count()} clients");
            foreach ($clients as $key => $client) {
                if (!empty($this->options->isTest) && $this->options->isTest == "true"){
                    if (($key) == 1) break;
                }

                $fileName = Helper::removeSpecialCharacters($client->qrcode);

                if (!empty($this->modelCard->file_name_template)) {
                    $fileName = $this->card()->getFileNameByCardTemplate($this->modelCard, $client);
                }

                $result = $this->generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension);
                $this->info(++$key.". Inserted {$client->name} - {$fileName}.{$extension}");
            }
        }

        $this->info("INSERT CARD => SUCCESS");
        return $result;
    }

    public function generateSingleCardByClient($client, $fileName, $bgPath, $savePath, $extension)
    {
        // $type = !empty($client->type) ? $client->type : null;

        // if (empty($type)) {
        //     return [];
        // }

        $bg = Image::make(Storage::disk('local')->path($bgPath));

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

                            $bg = $this->addQrcode($bg, $qrcodePath, $cardDetail);
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

                                $bg = $this->addQrcode($bg, $qrcodePath, $cardDetail);
                            }
                        }
                        break;
                    case CardDetail::TYPE_FIELD:
                        $field = $cardDetail->field;

                        if (in_array($field, ['name', 'email', 'phone'])) {
                            $value = $client->$field;

                            if ($field == "name" && $client->$field == "UNNAMED") {
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

        $bg->save(Storage::disk('local')->path("{$savePath}/{$fileName}.{$extension}"));

        /* test */

        return [
            'document_pdf' => $documentPdfPath
        ];
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

    public function addQrcode($background, $imagePath, $modelCardDetail)
    {
        $posX = round(($modelCardDetail->pos_x/100)*$background->width());
        $posY = round(($modelCardDetail->pos_y/100)*$background->height());

        $image = Image::make(Storage::disk('local')->path($imagePath))->resize($modelCardDetail->width, $modelCardDetail->height, function ($constraint) {
            $constraint->aspectRatio();
        });

        $background->insert(
            $image,
            $modelCardDetail->h_align.'-'.$modelCardDetail->v_align,
            $posX,
            $posY
        );

        return $background;
    }

    // private function getFileNameByCardTemplate($client)
    // {
    //     $datas = $client->getFullFields();
    //     unset($datas['avatar']);
    //     unset($datas['img_qrcode']);
    //     return Helper::generateQrcodeByTemplate($this->modelCard->file_name_template, $datas);
    // }
}
