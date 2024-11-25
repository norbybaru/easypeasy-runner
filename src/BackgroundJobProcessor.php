<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Data\BackgroundJobData;
use NorbyBaru\EasyRunner\Enum\PriorityEnum;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class BackgroundJobProcessor extends AbstractJob
{
    public function __construct(protected BackgroundJobRepository $repository)
    {
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput;
    }

    public function processQueues(): void
    {
        // Process jobs for each priority level
        foreach (PriorityEnum::values() as $priority) {
            $weight = $this->getPriorityWeight($priority);
            // Process more jobs from higher priority queues
            for ($i = 0; $i < $weight; $i++) {
                $job = $this->repository->getNextJob($priority);
                if ($job) {
                    $this->repository->processingJob($job->id);
                    $jobData = BackgroundJobData::fromObject($job);
                    $this->process($jobData);
                }
            }
        }
    }

    /**
     * Prepare job execution command
     *
     * @return void
     */
    private function process(BackgroundJobData $job)
    {
        $phpBinary = (new PhpExecutableFinder)->find() ?? 'php';
        $artisanPath = base_path('artisan');

        // Serialize parameters to pass to artisan command
        $serializedParams = base64_encode(serialize([
            'job_id' => $job->id,
            'class' => $job->class_name,
            'method' => $job->method_name,
            'params' => json_decode($job->parameters, true),
            'retry_attempts' => $job->max_attempts ?? $this->repository->getMaxRetries(),
        ]));

        // Construct command
        $command = [
            $phpBinary,
            $artisanPath,
            'background:job:exec',
            $serializedParams,
        ];
        // Run process
        $process = new Process($command);
        $process->setOptions(['create_new_console' => true]);
        $process->start();

        $this->output->writeln(sprintf('Processing [%s::%s]', $job->class_name, $job->method_name));

        $this->logJobStart(
            jobId: $job->id,
            className: $job->class_name,
            methodName: $job->method_name
        );
    }
}
