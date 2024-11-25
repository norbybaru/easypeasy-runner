<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use NorbyBaru\EasyRunner\BackgroundJobProcessor;

class BackgroundJobProcessorCommand extends Command
{
    protected $signature = 'background:jobs:process';

    protected $description = 'Process background jobs from the database queue';

    public function handle(BackgroundJobProcessor $processor): void
    {
        $this->info('Started background job processor');

        while (true) {
            DB::transaction(fn () => $processor->processQueues());
            sleep(1);
        }
    }
}
