<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use NorbyBaru\EasyRunner\Enum\PriorityEnum;
use ReflectionMethod;
use RuntimeException;
use Throwable;

abstract class AbstractJob
{
    use InteractsWithIO;

    protected function getPriorityWeight(string $priority): int
    {
        $priorityEnum = PriorityEnum::tryFrom($priority) ?? PriorityEnum::tryFrom($this->getDefaultPriority());

        return match ($priorityEnum) {
            PriorityEnum::HIGH => 1,
            PriorityEnum::MEDIUM => 5,
            PriorityEnum::LOW => 10,
        };
    }

    protected function getDefaultPriority()
    {
        return $this->config('default_priority');
    }

    protected function getAllowedNamespaces(): array
    {
        return $this->config('allowed_namespaces');
    }

    protected function config(string $key)
    {
        return config("easypeasy-runner.{$key}");
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
            if ($this->isNamespaceMatching($className, $namespace)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            throw new RuntimeException("Execution of class {$className} is not permitted.");
        }

        return true;
    }

    private function isNamespaceMatching(string $className, string $namespace): bool
    {
        // fully qualified class name provided as namespace
        if ($namespace === $className) {
            return true;
        }

        // Ensure the namespace ends with a backslash
        $namespace = rtrim($namespace, '\\').'\\';

        // Check if the class starts with the given namespace
        return stripos($className, $namespace) === 0;
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
        Log::channel('background_jobs')->info("Started {$className}::{$methodName} processing...", [
            'job_id' => $jobId,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobSuccess(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Done processing {$className}::{$methodName}", [
            'job_id' => $jobId,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobError(string $jobId, string $className, string $methodName, Throwable $exception)
    {
        Log::channel('background_jobs_errors')->error("Processing {$className}::{$methodName} failed", [
            'job_id' => $jobId,
            'exception' => $exception,
            'timestamp' => Carbon::now(),
        ]);
    }

    protected function logJobFailed(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->error("Failed processing {$className}::{$methodName} after max attempts", [
            'job_id' => $jobId,
            'timestamp' => Carbon::now(),
        ]);
    }
}
