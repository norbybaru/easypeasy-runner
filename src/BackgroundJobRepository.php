<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NorbyBaru\EasyRunner\Enum\StatusEnum;

class BackgroundJobRepository
{
    public function __construct(protected ConnectionInterface $connection) {}

    public function create(array $data): string
    {
        $data['id'] = $uuid = (string) Str::uuid();
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();

        $this->getBackgroundJobTable()->insert($data);

        return $uuid;
    }

    public function getJob(string $id): ?object
    {
        return $this->getBackgroundJobTable()
            ->where('id', $id)
            ->first();
    }

    public function getNextJob(string $priority): ?object
    {
        return $this->getBackgroundJobTable()
            ->where('status', StatusEnum::PENDING)
            ->where('priority', $priority)
            ->where('scheduled_at', '<=', Carbon::now())
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }

    public function processingJob(string $id): int
    {
        return $this->getBackgroundJobTable()
            ->where('id', $id)
            ->update([
                'status' => StatusEnum::PROCESSING,
                'attempts' => DB::raw('attempts + 1'),
                'started_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function completeJob(string $id): int
    {
        return $this->getBackgroundJobTable()
            ->where('id', $id)
            ->update([
                'status' => StatusEnum::COMPLETED,
                'completed_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function failJob(string $id, string $error): int
    {
        return $this->getBackgroundJobTable()
            ->where('id', $id)
            ->update([
                'status' => StatusEnum::FAILED,
                'error' => $error,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function resetJobForRetry(string $jobId, string $error): void
    {
        // Reset for retry
        $nextAttemptAt = Carbon::now()->addSeconds($this->getRetryDelay());
        $this->getBackgroundJobTable()
            ->where('id', $jobId)
            ->update([
                'status' => StatusEnum::PENDING,
                'scheduled_at' => $nextAttemptAt,
                'error' => $error,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function cleanup(?int $days = null): int
    {
        return $this->getBackgroundJobTable()
            ->whereIn('status', ['completed', 'failed'])
            ->when($days > 0, fn ($query) => $query->where('updated_at', '<', Carbon::now()->subDays($days)))
            ->delete();
    }

    public function getDefaultPriority()
    {
        return $this->config()['default_priority'];
    }

    public function getMaxRetries(): int
    {
        return $this->config()['max_retries'];
    }

    public function getRetryDelay(): int
    {
        return $this->config()['retry_delay'];
    }

    public function config()
    {
        return config('easypeasy-runner');
    }

    private function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    private function getBackgroundJobTable(): Builder
    {
        return $this->connection->table($this->getTable());
    }

    private function getTable(): string
    {
        return 'background_jobs';
    }
}
