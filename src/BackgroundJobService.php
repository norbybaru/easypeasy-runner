<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Support\Carbon;
use NorbyBaru\EasyRunner\Enum\StatusEnum;

class BackgroundJobService
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = [];
    }

    protected function getRepository(): BackgroundJobRepository
    {
        return resolve(BackgroundJobRepository::class);
    }

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

    public function createJob(
        string $className,
        string $methodName,
        array $params = [],
        array $options = []
    ): string {
        $scheduledAt = isset($options['delay'])
            ? Carbon::now()->addSeconds($options['delay'])
            : Carbon::now();

        // return $this->jobRepository->create([
        //     'class_name' => $className,
        //     'method_name' => $methodName,
        //     'parameters' => json_encode($params),
        //     'priority' => $options['priority'] ?? $this->config['default_priority'],
        //     'scheduled_at' => $scheduledAt,
        //     'max_attempts' => $options['retry_attempts'] ?? $this->config['max_retries'],
        //     'status' => StatusEnum::PENDING,
        // ]);

        return '';
    }

    public function getNextJob(string $priority): ?object
    {
        return $this->jobRepository->getNextJob($priority);
    }

    public function processJob(string $id): void
    {
        $this->jobRepository->processingJob($id);
    }
}
