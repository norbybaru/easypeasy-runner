<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Data\FactoryExecutor;
use NorbyBaru\EasyRunner\Facade\BackgroundJob;
use Throwable;

class JobExecutor extends AbstractJob
{
    public function execute(FactoryExecutor $factory)
    {
        try {
            $jobId = $factory->uuid;
            $className = $factory->className;
            $methodName = $factory->methodName;
            $params = $factory->params;
            $retriesLeft = $factory->retryAttempts;

            // Instantiate class and call method
            $instance = new $className;
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

    private function handleJobFailure(
        string $jobId,
        string $className,
        string $methodName,
        array $params,
        Throwable $exception,
        int $retriesLeft
    ) {
        // Log error
        $this->logJobError(
            jobId: $jobId,
            className: $className,
            methodName: $methodName,
            exception: $exception
        );

        // Retry mechanism
        if ($retriesLeft > 0) {
            // Schedule retry
            if ($this->getRetryDelay() > 0) {
                sleep($this->getRetryDelay());
            }

            BackgroundJob::run(
                className: $className,
                methodName: $methodName,
                params: $params,
                retryAttempts: $retriesLeft - 1
            );

            return;
        }

        $this->logJobFailed(jobId: $jobId, className: $className, methodName: $methodName);
    }
}
