<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Data\BackgroundJobData;
use NorbyBaru\EasyRunner\Data\FactoryExecutor;
use Throwable;

class BackgroundJobExecutor extends AbstractJob
{
    public function __construct(protected BackgroundJobRepository $repository) {}

    public function execute(FactoryExecutor $factory)
    {
        try {
            $job = $this->repository->getJob(id: $factory->uuid);
            $jobData = BackgroundJobData::fromObject($job);

            $className = $factory->className;
            $methodName = $factory->methodName;
            $params = $factory->params;

            // Instantiate class and call method
            $instance = new $className;
            $result = call_user_func_array([$instance, $methodName], $params);

            $this->handleJobCompletion(job: $jobData);

        } catch (Throwable $e) {
            // Handle job failure
            $this->handleJobFailure(
                job: $jobData,
                exception: $e,
            );
        }
    }

    public function handleJobCompletion(BackgroundJobData $job): void
    {
        $this->repository->completeJob(id: $job->id);

        $this->logJobSuccess(
            jobId: $job->id,
            className: $job->class_name,
            methodName: $job->method_name
        );
    }

    private function handleJobFailure(
        BackgroundJobData $job,
        Throwable $exception,
    ) {
        // Log error
        $this->logJobError(
            jobId: $job->id,
            className: $job->class_name,
            methodName: $job->method_name,
            exception: $exception
        );

        if ($job->attempts >= $job->max_attempts) {
            $this->repository->failJob(
                id: $job->id,
                error: $exception->getMessage()
            );

            $this->logJobFailed(
                jobId: $job->id,
                className: $job->class_name,
                methodName: $job->method_name
            );
        } else {
            $this->repository->resetJobForRetry(
                jobId: $job->id,
                error: $exception->getMessage()
            );
        }
    }
}
