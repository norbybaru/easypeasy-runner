<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner\Data;

use Illuminate\Support\Carbon;

readonly class BackgroundJobData
{
    public function __construct(
        public string $id,
        public string $class_name,
        public string $method_name,
        public string $priority,
        public string $status,
        public int $attempts,
        public int $max_attempts,
        public ?string $error = null,
        public ?string $parameters = null,
        public ?Carbon $scheduled_at = null,
        public ?Carbon $started_at = null,
        public ?Carbon $completed_at = null,
    ) {}

    public static function fromObject(object $object): self
    {
        return new self(
            id: $object->id,
            class_name: $object->class_name,
            method_name: $object->method_name,
            priority: $object->priority,
            status: $object->status,
            attempts: $object->attempts,
            max_attempts: $object->max_attempts,
            error: $object->error,
            parameters: $object->parameters,
            scheduled_at: $object->scheduled_at ? Carbon::parse($object->scheduled_at) : null,
            started_at: $object->started_at ? Carbon::parse($object->started_at) : null,
            completed_at: $object->completed_at ? Carbon::parse($object->completed_at) : null,
        );
    }
}
