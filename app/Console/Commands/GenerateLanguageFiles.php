<?php

namespace App\Console\Commands;

use App\HttpClient\HttpClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use App\Models\Event; // Make sure this path is correct
use Illuminate\Support\Facades\Log;

class GenerateLanguageFiles extends Command
{
    private $httpClient;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:generate {eventCode? : The code of the event to generate translations for (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates or updates language data for a specific event (or all events) in event-based files.';

    public function __construct()
    {
        parent::__construct();
        $this->httpClient = new HttpClient((env('APP_ENV') == "production" ? "https://" : "").env("REGISER_DOMAIN")."/api/v1", [
            "Accept"                    => "application/json",
            "User-Agent"                => "ApiPortal",
            "App-Key"                   => env("REGISER_APP_KEY"),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $eventCode = $this->argument('eventCode');
        $fileName = "lp";
        $targetEventId = null; // Initialize target event ID

        // 1. Determine the target event if eventCode is provided
        if ($eventCode) {
            $this->info("Attempting to find event with code: {$eventCode}");
            $event = Event::where('code', $eventCode)->first();

            if (!$event) {
                $this->error("Event with code '{$eventCode}' not found.");
                return Command::FAILURE;
            }

            $targetEventId = $event->id;
            $this->info("Found event: ID {$targetEventId}, Code {$eventCode}");
        } else {
            $this->info('Generating language files for all events...');
        }

        // 2. Query Translations from language_defines
        try {
            $query = DB::table('language_defines') // <-- Your translations table
                ->join('languages', 'language_defines.language_id', '=', 'languages.id') // <-- Your languages table
                ->join('events', 'language_defines.event_id', '=', 'events.id') // Join with events table
                ->select(
                    'language_defines.event_id', // Keep event_id for potential future use if needed
                    'events.code as event_code', // Select the event code
                    'languages.code as language_code',
                    'language_defines.keyword',
                    'language_defines.translate'
                )
                ->whereNotNull('events.code'); // Only include translations for events that have a code


            if ($targetEventId !== null) {
                $query->where('language_defines.event_id', $targetEventId);
            }

            $allTranslations = $query->get();

        } catch (\Exception $e) {
             $this->error("Error querying database: " . $e->getMessage());
             return Command::FAILURE;
        }


        if ($allTranslations->isEmpty()) {
            $message = "No translations found";
            if ($targetEventId !== null) {
                // Modify message to reflect searching by event code
                $message .= " for event with ID {$targetEventId} (code: {$eventCode})";
            }
            $this->info($message . ".");
            // Optionally, you might want to delete the file for this event/language if no translations exist.
            return Command::SUCCESS;
        }

        // 3. Group translations by language_code and then by event_code
        $translationsByLanguageAndEventCode = $allTranslations->groupBy('language_code')->map(function ($langGroup) {
            // Group by event_code instead of event_id
            return $langGroup->groupBy('event_code');
        });

        // 4. Process Each Language and Event Group
        foreach ($translationsByLanguageAndEventCode as $languageCode => $eventsGroup) {
            $outputPath = lang_path("{$languageCode}/{$fileName}.php"); // e.g., lang/vi/{fileName}.php

            $existingLangData = [];

            // Load existing language data if the file exists
            if (File::exists($outputPath)) {
                try {
                    $existingLangData = include $outputPath;
                    if (!is_array($existingLangData)) {
                        $existingLangData = []; // Reset if file content is not an array
                    }
                } catch (\ParseError $e) {
                    $this->warn("Failed to parse existing language file {$outputPath}. It might be corrupted. Overwriting.");
                    $existingLangData = []; // Discard corrupted data
                }
            }

            // Process events for the current language
            foreach ($eventsGroup as $eventCode => $translations) {
                $eventLangData = [];
                foreach ($translations as $translation) {
                    // Use Arr::set to handle dot notation and build nested array within the event group
                    Arr::set($eventLangData, $translation->keyword, $translation->translate);
                }
                // Update or add the data for this event into the existing data
                // Using 'event_' prefix for clarity in the lang file
                $existingLangData[$eventCode] = $eventLangData;
            }

            // 5. Generate the PHP file content
            $fileContent = "<?php\n\nreturn " . var_export($existingLangData, true) . ";\n";

            // 6. Write the content to the file
            try {
                // Ensure the language directory exists
                File::ensureDirectoryExists(lang_path($languageCode));

                File::put($outputPath, $fileContent);

                $this->syncToRegister($languageCode, $fileName, $existingLangData); // Sync to register page
                $this->info("Updated language file: {$outputPath}");
            } catch (\Exception $e) {
                $this->error("Failed to write language file {$outputPath}: " . $e->getMessage());
                return Command::FAILURE; // Stop execution on write error
            }
        }

        $this->info("Language file generation/update process completed.");
        return Command::SUCCESS;
    }

    private function syncToRegister(string $lang, string $file, array $data)
    {
        $this->line("Syncing language files to register page...");
        Log::info("Syncing language files to register page...");
        $result = $this->httpClient->post("language_defines/{$lang}/{$file}", [
            "data" => $data
        ]);

        if (count($result)) {
            $this->line("Received: {$result['message']}");
            Log::info("Received: {$result['message']}");
        }

        return true;
    }
}
