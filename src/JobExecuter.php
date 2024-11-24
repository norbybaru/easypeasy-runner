<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Throwable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use NorbyBaru\EasyRunner\Facade\BackgroundJob;

class JobExecuter
{
    public function execute(string $serializedData)
    {
        try {
            // Unserialize job data
            $jobData = unserialize(base64_decode($serializedData));

            // Extract job details
            $jobId = $jobData['job_id'];
            $className = $jobData['class'];
            $methodName = $jobData['method'];
            $params = $jobData['params'];
            $retriesLeft = $jobData['retry_attempts'];

            // Instantiate class and call method
            $instance = new $className();
            $result = call_user_func_array([$instance, $methodName], $params);

            // Log successful execution
            $this->logJobSuccess(jobId: $jobId, className: $className, methodName: $methodName);
        } catch (Throwable $e) {
            // Handle job failure
            $this->handleJobFailure(
                jobId: $jobId,
                className: $className,
                methodName: $methodName,
                params: $params,
                exception: $e,
                retriesLeft: $retriesLeft
            );
        }
    }

    private function logJobSuccess(string $jobId, string $className, string $methodName)
    {
        Log::channel('background_jobs')->info("Job Completed Successfully", [
            'job_id' => $jobId,
            'class' => $className,
            'method' => $methodName,
            'timestamp' => Carbon::now(),
        ]);
    }

    private function handleJobFailure(
        string $jobId,
        string $className,
        string $methodName,
        array $params,
        Throwable $exception,
        int $retriesLeft
    ) {
        // Log error
        Log::channel('background_jobs_errors')->error("Job Failed", [
            'job_id' => $jobId,
            'class' => $className,
            'method' => $methodName,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => Carbon::now()
        ]);

        // Retry mechanism
        if ($retriesLeft > 0) {
            // Schedule retry
            sleep(config('easypeasy-runner.retry_delay'));
            BackgroundJob::run(className: $className, methodName: $methodName, params: $params, retryAttempts: $retriesLeft - 1);
        }
    }
}
