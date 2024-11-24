<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class BackgroundJobRunner extends AbstractJob
{
    public function run(string $className, string $methodName, array $params = [], ?int $retryAttempts = null): string
    {
        // Validate inputs
        $this->validateClassName($className);
        $this->validateMethodName($className, $methodName);

        // Generate unique job identifier
        $jobId = $this->generateJobId();

        // Prepare job execution command
        $phpBinary = (new PhpExecutableFinder)->find() ?? 'php';
        $artisanPath = base_path('artisan');

        // Serialize parameters to pass to artisan command
        $serializedParams = base64_encode(serialize([
            'job_id' => $jobId,
            'class' => $className,
            'method' => $methodName,
            'params' => $params,
            'retry_attempts' => $retryAttempts ?? $this->getMaxRetries(),
        ]));

        // Construct command
        $command = [
            $phpBinary,
            $artisanPath,
            'background-process:run',
            $serializedParams,
        ];

        // Run process
        $process = new Process($command);
        $process->setOptions(['create_new_console' => true]);
        $process->start();

        // Log job initiation
        $this->logJobStart($jobId, $className, $methodName);

        return $jobId;
    }
}
