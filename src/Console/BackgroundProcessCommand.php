<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use NorbyBaru\EasyRunner\BackgroundJobRunner;

class BackgroundProcessCommand extends Command
{
    protected $signature = 'background-process:run {jobData}';
    protected $description = 'Run a background job';

    public function handle(BackgroundJobRunner $jobRunner)
    {
        $jobData = $this->argument(key: 'jobData');
        $jobRunner->executeJob(serializedData: $jobData);
    }
}