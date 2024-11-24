<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class BackgroundJobRunner
{
    public function __construct(protected readonly array $config) {}

    private function getAllowedNamespaces(): array
    {
        return $this->config['allowed_namespaces'];
    }

    private function getMaxRetries(): int
    {
        return $this->config['max_retries'];
    }

    public function run(string $className, string $methodName, array $params = [], ?int $retryAttempts = null): string
    {
        // Validate inputs
        $this->validateClassName($className);
        $this->validateMethodName($className, $methodName);

        // Generate unique job identifier
        $jobId = uniqid('job_', true);

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

    private function logJobStart(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Job Processing {$className}...", [
            'job_id' => $jobId,
            'class' => $className,
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    private function logJobSuccess(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info('Job Completed Successfully', [
            'job_id' => $jobId,
            'class' => $className,
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    private function validateClassName(string $className): bool
    {
        // Check if class exists
        if (! class_exists($className)) {
            throw new Exception("Class {$className} does not exist.");
        }

        // Validate against allowed namespaces
        $allowed = false;
        foreach ($this->getAllowedNamespaces() as $namespace) {
            if (str_starts_with($className, $namespace)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            throw new Exception("Execution of class {$className} is not permitted.");
        }

        return true;
    }

    private function validateMethodName(string $className, string $methodName): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($className, $methodName);

            // Ensure method is public
            if (! $reflectionMethod->isPublic()) {
                throw new Exception("Method {$methodName} is not publicly accessible.");
            }
        } catch (\ReflectionException $e) {
            throw new Exception("Method {$methodName} does not exist in class {$className}.");
        }

        return true;
    }
}
