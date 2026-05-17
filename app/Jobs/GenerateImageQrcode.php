<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GenerateImageQrcode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventCode;
    public $qrcode;
    public $type;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $eventCode, ?string $qrcode = null, ?string $type = null)
    {
        $this->eventCode = $eventCode;
        $this->qrcode = $qrcode;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = "generate:image-qrcode {$this->eventCode} 1";

        if (isset($this->qrcode)) {
            $command .= " --qrcode={$this->qrcode}";
        }

        if (isset($this->type)) {
            $command .= " --type={$this->type}";
        }

        Artisan::call($command);
    }
}
