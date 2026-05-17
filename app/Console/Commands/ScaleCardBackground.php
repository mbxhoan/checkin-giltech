<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Admin\CardService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Image;
use Imagick;

class ScaleCardBackground extends Command
{
    private $modelCard;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'scale:card {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scale card background';

    public function card()
    {
        return new CardService();
    }

    public function handle()
    {
        $id = $this->argument('id');
        $this->modelCard = $this->card()->repo->getItem($id);

        /* START */

        $start = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");

        if (!empty($this->modelCard)) {
            // $extension = pathinfo($bgPath, PATHINFO_EXTENSION);
            $imgPath = "public/{$this->modelCard->background}";
            $fileName = basename($imgPath);
            $fileInfo = pathinfo($imgPath);
            $fileNameNoExtension = $fileInfo['filename'];
            $saveFolderImagick = dirname($this->modelCard->background);
            $saveFolderNormalImage = dirname($imgPath);

            if (Storage::exists($imgPath)) {
                try {
                    $this->line("Scaling...");
                    // $image = new Imagick(Image::make(Storage::disk('local')->path($imgPath)));
                    $image = new Imagick(Storage::disk('local')->path($imgPath));
                    $image->resizeImage($this->modelCard->scaled, 0, Imagick::FILTER_LANCZOS2, 1);
                    $image->writeImage("public/storage/{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}");

                    /* SCALE USING NORMAL IMAGE */

                    // $img = Image::make(Storage::disk('local')->path($imgPath))->resize($this->modelCard->scaled, $this->modelCard->scaled, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // });

                    // $img->save(Storage::disk('local')->path("{$saveFolderNormalImage}/{$fileNameNoExtension}.{$this->modelCard->extension}"));
                    // $img->save(Storage::disk('local')->path("{$saveFolderNormalImage}/{$fileNameNoExtension}.{$this->modelCard->extension}"));

                    $this->modelCard->update([
                        'background' => "{$saveFolderImagick}/{$fileNameNoExtension}.{$this->modelCard->extension}"
                    ]);

                    $this->info("Scaled {$fileNameNoExtension}.{$this->modelCard->extension}");
                } catch (Exception $e) {
                    Log::alert($e);
                    Log::info("Error occurred on scale image {$fileName}");
                    $this->error("Executed with error(s): {$e->getMessage()}");
                }
            } else {
                $this->error('NO FOUND IMAGE');
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
}
