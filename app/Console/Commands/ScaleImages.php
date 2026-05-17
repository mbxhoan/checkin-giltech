<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;
use App\Services\Admin\CardService;
use App\Services\Admin\ClientService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Imagick;

class ScaleImages extends Command
{
    private $options;
    private $modelCard;
    private $scaled;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'scale:images {cardId} {--clientId=} {--scaled=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scale images by card ID';

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
        $this->options = (object)$this->options();
        $cardId = $this->argument('cardId');
        $this->modelCard = $this->card()->findById($cardId);
        $this->scaled = (!empty($this->options->scaled) && is_numeric($this->options->scaled)) ? $this->options->scaled : 1000;

        /* START */

        $start = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");

        if (!empty($this->modelCard)) {
            try {
                $clients = $this->client()->getListByAttributes(array_filter([
                    'event_id'  => $this->modelCard->event_id,
                    'type'      => $this->modelCard->client_type
                ]));

                $this->scale($clients);
            } catch (Exception $e) {
                Log::alert($e);
                Log::info("Error occurred on scaling cards #{$this->modelCard->id} - {$this->modelCard->code}");
                $this->error("Executed with error(s). {$e->getMessage()}");
            }
        } else {
            $this->error('NO FOUND CARD');
        }

        /* END */

        $end = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");
        $this->comment("Finished at {$end}");
        $diff = (strtotime($end) - strtotime($start))/60;
        $this->comment("Collapsed time: {$diff} minutes !");
        $this->question('COMPLETED');
        return true;
    }

    public function scale($clients)
    {
        if (!empty($this->options->clientId) && is_numeric($this->options->clientId)){
            $clientId = $this->options->clientId;
            $client = $this->client()->findById($clientId);

            if (!empty($client)) {
                $result = $this->scaleSingleImage($client, 0);
            }
        } else {
            foreach ($clients as $key => $client) {
                $result = $this->scaleSingleImage($client, $key);
            }
        }

        $this->info("SCALED IMAGES => SUCCESS");
        return $result;
    }

    public function scaleSingleImage($client, int $key)
    {
        $key = ++$key;
        $imgPath = "public/{$client->document_pdf}";
        $fileName = basename($imgPath);
        $fileInfo = pathinfo($imgPath);
        $fileNameNoExtension = $fileInfo['filename'];
        $saveFolderImagick = dirname($client->document_pdf);

        if (Storage::exists($imgPath)) {
            try {
                $image = new Imagick(Storage::disk('local')->path($imgPath));
                $image->resizeImage($this->scaled, 0, Imagick::FILTER_LANCZOS2, 1);
                $image->writeImage("public/storage/{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}");
                // Storage::delete("public/{$client->document_pdf}");

                if ($this->modelCard->device == Card::DEVICE_BOTH) {
                    $client->update([
                        'card_link_mobile'  => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}",
                        'card_link_desktop' => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}",
                        'document_pdf'      => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}"
                    ]);
                } else if ($this->modelCard->device == Card::DEVICE_DESKTOP) {
                    $client->update([
                        'card_link_desktop' => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}"
                    ]);
                } else {
                    $client->update([
                        'card_link_mobile'  => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}"
                    ]);
                }

                $this->info("{$key}. Scaled {$client->name} - {$fileNameNoExtension}.{$this->modelCard->extension}");
                // $this->info("Scaled {$fileNameNoExtension}.{$this->modelCard->extension}");
            } catch (Exception $e) {
                Log::alert($e);
                Log::info("Error occurred on scale image {$fileName}");
                $this->error("Executed with error(s): {$e->getMessage()}");
            }
        } else {
            $this->error("{$key}. NO FOUND IMAGE");
        }
    }
}
