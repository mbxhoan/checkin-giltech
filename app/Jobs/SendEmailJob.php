<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $campaignId;
    public $emailId;
    public $timeout = 300;
    public $tries = 3;
    public $backoff = [15, 45, 120];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?int $campaignId = 0, ?int $emailId = 0)
    {
        $this->campaignId = $campaignId;
        $this->emailId = $emailId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call("send:mail --campaignId={$this->campaignId} --emailId={$this->emailId}");
    }
}
