<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use NorbyBaru\EasyRunner\BackgroundJobProcessor;

class BackgroundJobCleanupCommand extends Command
{
    protected $signature = 'background:jobs:cleanup {--days=}';

    protected $description = 'Clean up old completed and failed jobs';

    public function handle(BackgroundJobProcessor $processor)
    {
        $this->info('Cleaning up background jobs');

        $days = (int) $this->option('days');

        if (! $processor->cleanup($days)) {
            $this->error('Failed to clean up background jobs');

            return;
        }

        $this->info('Cleaned up background jobs completed');
    }
}
