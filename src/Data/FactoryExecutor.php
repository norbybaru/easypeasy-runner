<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Data;

readonly class FactoryExecutor
{
    public function __construct(
        public string $uuid,
        public string $className,
        public string $methodName,
        public array $params = [],
        public ?int $retryAttempts = null,
        public ?int $retryDelay = null
    ) {}

    public static function fromSerializedPayload(string $payload): self
    {
        $jobData = unserialize(base64_decode($payload));

        return new self(
            uuid: $jobData['job_id'],
            className: $jobData['class'],
            methodName: $jobData['method'],
            params: $jobData['params'],
            retryAttempts: $jobData['retry_attempts'] ?? null,
            retryDelay: $jobData['retry_delay'] ?? null
        );
    }
}
