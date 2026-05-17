<?php

namespace App\Console\Commands\Videc;

use App\Services\Videc\RegistrationFileService;
use Illuminate\Console\Command;

class CleanupRegistrationFiles extends Command
{
    protected $signature = 'videc:cleanup-registration-files {--hours=24 : Remove temporary uploads older than this many hours}';

    protected $description = 'Cleanup unattached temporary registration file uploads.';

    public function __construct(private readonly RegistrationFileService $registrationFileService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config('registration_files.cleanup_enabled', true)) {
            $this->info('Registration file cleanup is disabled by config.');
            return Command::SUCCESS;
        }

        $hours = max(1, (int) $this->option('hours'));
        $result = $this->registrationFileService->cleanupExpiredTemporaryFiles($hours);

        $this->info(sprintf(
            'Cleanup completed. matched=%d, deleted_from_storage=%d',
            (int) ($result['matched'] ?? 0),
            (int) ($result['deleted_from_storage'] ?? 0),
        ));

        return Command::SUCCESS;
    }
}
