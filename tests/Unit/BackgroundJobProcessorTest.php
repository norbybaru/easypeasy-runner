<?php

namespace NorbyBaru\EasyRunner\Tests\Unit;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use NorbyBaru\EasyRunner\BackgroundJobExecutor;
use NorbyBaru\EasyRunner\BackgroundJobProcessor;
use NorbyBaru\EasyRunner\BackgroundJobRunner;
use NorbyBaru\EasyRunner\Data\BackgroundJobData;
use NorbyBaru\EasyRunner\Enum\StatusEnum;
use NorbyBaru\EasyRunner\Tests\Fixture\DummyAction;
use NorbyBaru\EasyRunner\Tests\TestCase;

class BackgroundJobProcessorTest extends TestCase
{
    protected BackgroundJobRunner $jobRunner;

    protected BackgroundJobProcessor $jobProcessor;

    protected BackgroundJobExecutor $jobExecutor;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('easypeasy-runner.allowed_namespaces', [DummyAction::class]);

        $this->jobRunner = resolve(BackgroundJobRunner::class);
        $this->jobProcessor = resolve(BackgroundJobProcessor::class);
        $this->jobExecutor = resolve(BackgroundJobExecutor::class);
    }

    public function test_it_should_set_job_processing()
    {
        $jobId = $this->jobRunner->run(
            DummyAction::class,
            'execute',
            ['param1', 'param2']
        );

        $this->jobProcessor->processQueues();

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals(StatusEnum::PROCESSING->value, $job->status);
        $this->assertNotNull($job->started_at);
    }

    public function test_job_failure_and_retry()
    {
        $jobId = $this->jobRunner->run(
            className: DummyAction::class,
            methodName: 'execute',
            params: ['param1', 'param2'],
            options: ['retry_attempts' => 2]
        );

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        // Simulate first failure
        $this->repository->processingJob($job->id);
        $this->jobExecutor->handleJobFailure(
            job: BackgroundJobData::fromObject($job),
            exception: new \Exception('Test error message')
        );

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals('pending', $job->status);
        $this->assertEquals(1, $job->attempts);
        $this->assertEquals('Test error message', $job->error);

        // Simulate second failure
        $this->repository->processingJob($job->id);
        $this->jobExecutor->handleJobFailure(
            job: BackgroundJobData::fromObject($job),
            exception: new \Exception('Test error message')
        );

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals('pending', $job->status);
        $this->assertEquals(2, $job->attempts);
        $this->assertEquals('Test error message', $job->error);

        $this->repository->processingJob($job->id);
        $this->jobExecutor->handleJobFailure(
            job: BackgroundJobData::fromObject($job),
            exception: new \Exception('Test error message')
        );

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals('failed', $job->status);
    }

    public function test_delayed_job_scheduling()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // Create delayed job
        $jobId = $this->jobRunner->run(
            className: DummyAction::class,
            methodName: 'execute',
            params: ['param1', 'param2'],
            options: ['delay' => 3600]
        );

        // Verify job is not processed before delay
        $this->jobProcessor->processQueues();

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals('pending', $job->status);

        // Move time forward
        Carbon::setTestNow($now->addSeconds(3601));

        // Verify job is processed after delay
        $this->jobProcessor->processQueues();

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals('processing', $job->status);
    }
}
