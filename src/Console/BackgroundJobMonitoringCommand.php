<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use NorbyBaru\EasyRunner\BackgroundJobRepository;
use NorbyBaru\EasyRunner\Enum\StatusEnum;

class BackgroundJobMonitoringCommand extends Command
{
    protected $signature = 'background:jobs:stats
                            {--monitor : Monitor background jobs}
                            {--failed : Display only failed jobs}
                            {--pending : Display only pending jobs}
                            {--processing : Display only processing jobs}
                            {--completed : Display only completed jobs}';

    protected $description = 'Monitor background jobs';

    public function handle(BackgroundJobRepository $repository): void
    {
        $this->info('Started background job monitoring');

        $headers = ['ID', 'Status', 'Priority', 'Attempts', 'Scheduled At', 'Started At', 'Completed At'];

        $rows = $this->query($repository)
            ->get()
            ->map(fn ($data) => (array) $data)
            ->toArray();

        if ($this->option('monitor')) {
            while (true) {
                system('clear');
                $this->displayStats($headers, $rows);
                sleep(5);
            }
        }

        $this->displayStats($headers, $rows);
    }

    private function displayStats(array $headers, array $rows): void
    {
        $this->table(
            $headers,
            $rows
        );
    }

    private function query(BackgroundJobRepository $repository): Builder
    {
        return $repository->getMonitoringQuery()
            ->when($this->option('failed'), fn ($query) => $query->where('status', StatusEnum::FAILED))
            ->when($this->option('pending'), fn ($query) => $query->where('status', StatusEnum::PENDING));
    }
}
