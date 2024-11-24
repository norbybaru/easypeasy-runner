<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use NorbyBaru\EasyRunner\Data\FactoryExecutor;
use NorbyBaru\EasyRunner\JobExecutor;

class BackgroundProcessCommand extends Command
{
    protected $signature = 'background-process:run {jobData}';

    protected $description = 'Run a background job';

    public function handle(JobExecutor $jobExecuter)
    {
        $jobData = $this->argument(key: 'jobData');

        $factory = FactoryExecutor::fromSerializedPayload(payload: $jobData);
        $jobExecuter->execute(factory: $factory);
    }
}
