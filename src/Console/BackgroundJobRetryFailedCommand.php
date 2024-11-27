<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use NorbyBaru\EasyRunner\BackgroundJobRepository;
use NorbyBaru\EasyRunner\Enum\StatusEnum;
use Symfony\Component\Console\Helper\ProgressBar;

class BackgroundJobRetryFailedCommand extends Command
{
    protected $signature = 'background:jobs:retry-failed
                            {--id= : ID of the failed job to retry}';

    protected $description = 'Retry failed background jobs';

    protected ProgressBar $progressBar;

    public function handle(BackgroundJobRepository $repository): void
    {
        $this->info('Retrying failed background jobs');

        $total = $this->query($repository)->count();
        $this->progressBar = $this->output->createProgressBar($total);
        $this->progressBar->start();

        $id = $this->option('id');
        $repository->retryFailedJobs(id: $id);

        $this->progressBar->finish();
        $this->info("\nDispatched {$total} failed background jobs");
    }

    private function query(BackgroundJobRepository $repository): Builder
    {
        return $repository->getMonitoringQuery()
            ->select('id')
            ->where('status', StatusEnum::FAILED)
            ->when($this->option('id'), fn ($query) => $query->where('id', $this->option('id')))
            ->orderBy('created_at');
    }
}
