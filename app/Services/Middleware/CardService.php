<?php

namespace App\Services\Middleware;

use App\Jobs\GenerateCard;
use App\Models\Card;
use App\Services\BaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Card::class);
    }

    public function generate($cardId, $clientId = null)
    {
        /* Call Job Importing */
        $objJob = new GenerateCard($cardId, $clientId);
        $objJob->timeout = 600;
        $generateCardJob = $objJob->delay(Carbon::now()->addSeconds(1));
        dispatch($generateCardJob);
        return true;
    }

    public function generateCardNow($cardId, $clientId = null)
    {
        try {
            Artisan::call("generate:cards {$cardId} --clientId={$clientId}");
            // Artisan::call("scale:images {$cardId} --clientId={$clientId} --scaled=1000");
        } catch (Exception $e) {
            Log::alert($e->getMessage());
            return [
                'status'    => false,
                'msg'       => $e->getMessage()
            ];
        }

        return [
            'status'    => true,
            'msg'       => null,
        ];
    }
}
