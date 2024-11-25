<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Support\Carbon;
use NorbyBaru\EasyRunner\Enum\StatusEnum;

class BackgroundJobRunner extends AbstractJob
{
    public function __construct(protected BackgroundJobRepository $repository) {}

    public function run(
        string $className,
        string $methodName,
        array $params = [],
        array $options = []
    ): string {
        // Validate inputs
        $this->validateClassName($className);
        $this->validateMethodName($className, $methodName);

        return $this->createJob(
            className: $className,
            methodName: $methodName,
            params: $params,
            options: $options
        );
    }

    private function createJob(
        string $className,
        string $methodName,
        array $params = [],
        array $options = []
    ): string {
        $scheduledAt = isset($options['delay'])
            ? Carbon::now()->addSeconds($options['delay'])
            : Carbon::now();

        return $this->repository->create([
            'class_name' => $className,
            'method_name' => $methodName,
            'parameters' => json_encode($params),
            'priority' => $options['priority'] ?? $this->repository->getDefaultPriority(),
            'scheduled_at' => $scheduledAt,
            'max_attempts' => $options['retry_attempts'] ?? $this->repository->getMaxRetries(),
            'status' => StatusEnum::PENDING,
        ]);
    }
}
