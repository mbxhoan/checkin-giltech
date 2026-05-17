<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Services\Admin\ClientService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateCardHidec2025 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    /* customize */
    /* hidec-2025 */
    protected $signature = 'app:generate-card-hidec2025 {cardId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function client()
    {
        return new ClientService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cardId = !empty($this->argument('cardId')) ? $this->argument('cardId') : '';
        $card = Card::where('id', $cardId)->first();

        if ($card) {
            $clients = $this->client()->getListByAttributes(array_filter([
                'event_id'  => $card->event_id,
                'type'      => $card->client_type
            ]));

            foreach ($clients as $index => $client) {
                Artisan::call("generate:cards {$cardId} --clientId={$client->id}");;
                $this->info(++$index.". Inserted {$client->name}");
            }
        }

        return 1;
    }
}
