<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use NorbyBaru\EasyRunner\BackgroundJobExecutor;
use NorbyBaru\EasyRunner\Data\FactoryExecutor;

class BackgroundJobExecutorCommand extends Command
{
    protected $hidden = true;

    protected $signature = 'background:job:exec {jobData}';

    protected $description = 'Execute a background job';

    public function handle(BackgroundJobExecutor $executor)
    {
        $jobData = $this->argument(key: 'jobData');

        $factory = FactoryExecutor::fromSerializedPayload(payload: $jobData);
        $executor->execute(factory: $factory);
    }
}
