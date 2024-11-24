<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use RuntimeException;
use Throwable;

abstract class AbstractJob
{
    public function __construct(protected readonly array $config) {}

    protected function getAllowedNamespaces(): array
    {
        return $this->config['allowed_namespaces'];
    }

    protected function getMaxRetries(): int
    {
        return $this->config['max_retries'];
    }

    protected function getRetryDelay(): int
    {
        return $this->config['retry_delay'];
    }

    protected function generateJobId(): string
    {
        return uniqid('job_', true);
    }

    protected function validateClassName(string $className): bool
    {
        // Check if class exists
        if (! class_exists($className)) {
            throw new RuntimeException("Class {$className} does not exist.");
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
            throw new RuntimeException("Execution of class {$className} is not permitted.");
        }

        return true;
    }

    protected function validateMethodName(string $className, string $methodName): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($className, $methodName);

            // Ensure method is public
            if (! $reflectionMethod->isPublic()) {
                throw new RuntimeException("Method {$methodName} is not publicly accessible.");
            }
        } catch (\ReflectionException $e) {
            throw new RuntimeException("Method {$methodName} does not exist in class {$className}.");
        }

        return true;
    }

    protected function logJobStart(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Start processing {$className}...", [
            'job_id' => $jobId,
            'class' => get_class(new $className),
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobSuccess(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Done processing {$className}", [
            'job_id' => $jobId,
            'class' => get_class(new $className),
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobError(string $jobId, string $className, string $methodName, Throwable $exception)
    {
        Log::channel('background_jobs_errors')->error($exception, [
            'job_id' => $jobId,
            'class' => get_class(new $className),
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobFailed(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Failed processing {$className}", [
            'job_id' => $jobId,
            'class' => get_class(new $className),
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }
}
