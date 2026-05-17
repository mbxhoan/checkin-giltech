<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GenerateCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cardId;
    protected $isTest;
    protected $clientId;
    public $timeout;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $cardId, ?int $clientId = null)
    {
        $this->cardId = $cardId;
        $this->clientId = $clientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cmd = "generate:cards {$this->cardId} --clientId=0";

        if ($this->clientId) {
            $cmd = "generate:cards {$this->cardId} --clientId={$this->clientId}";
        }

        Artisan::call($cmd);
    }
}
