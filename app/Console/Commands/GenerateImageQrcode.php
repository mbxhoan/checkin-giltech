<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\Admin\ClientService;

class GenerateImageQrcode extends Command
{
    protected $event;
    protected $options;
    protected $service;
    protected $onlyEmpty = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:image-qrcode {eventCode} {onlyEmpty?} {--qrcode=} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate qrcodes of clients list join event';

    public function __construct(ClientService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->options = (object)$this->options();
        $this->onlyEmpty = (!empty($this->argument('onlyEmpty')) && $this->argument('onlyEmpty')) ? true : false;
        $eventCode = $this->argument('eventCode');

        $this->event = $this->service->event()->findByAttributes([
            'code' => $eventCode
        ]);

        if (empty($this->event)) {
            $this->error('Không tìm thấy sự kiện');
            return 0;
        }

        $attributes = [
            'event_id' => $this->event->id
        ];

        if (!empty($this->options->qrcode) && is_string($this->options->qrcode)) {
            $this->options->qrcode = $this->options->qrcode === "null" ? null : $this->options->qrcode;
            $attributes['qrcode'] = $this->options->qrcode;
            $client = $this->service->findByAttributes($attributes ?? []);

            if ($client) {
                /* tạo mã */
                $this->generateQrcode($client, 0);
            }
        } else {
            if (!empty($this->options->type) && is_string($this->options->type)) {
                $this->options->type = $this->options->type === "null" ? null : $this->options->type;
                if ($this->options->type) {
                    $attributes['type'] = $this->options->type;
                }
            }

            $clients = $this->service->getListByAttributes($attributes ?? []);

            if ($this->onlyEmpty) {
                $clients = $clients->whereNull('img_qrcode');
            }

            if ($clients) {
                $bar = $this->output->createProgressBar(count($clients));

                foreach ($clients as $index => $client) {
                    /* tạo mã */
                    $this->generateQrcode($client, $index);
                    $bar->advance();
                }

                $bar->finish();
            }
        }

        $this->info("\nGenerate Qrcodes successfully!");
        return true;
    }

    public function generateQrcode($client, int $index = 0)
    {
        try {
            /* Remove tmp Qr code */
            if ($client->img_qrcode) {
                $oldFile = storage_path("app/public/{$client->img_qrcode}");
                File::delete($oldFile);
            }

            $qrcodePath = $this->event->generateImgQrcodeOnSetting(
                $client->qrcode,
                $client->phone,
                $client->email,
                $client->name,
                $client->custom_fields ?? [],
            );
            // $this->info(++$index.". Generated {$client->qrcode} - {$qrcodePath}");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }

        if ($qrcodePath) {
            $client->update([
                'img_qrcode' => $qrcodePath
            ]);
        } else {
            $this->error("\n".++$index.". Failed");
        }

        return $qrcodePath ?? null;
    }
}
