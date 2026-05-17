<?php

namespace App\Jobs;

use App\Services\Admin\LanguageDefineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class GenerateLanguageDefineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventCode = null;
    public $service;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?string $eventCode = null)
    {
        $this->eventCode = $eventCode;
        $this->service = app(LanguageDefineService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->service->generateLanguageDefinesByCmd($this->eventCode);
    }
}
