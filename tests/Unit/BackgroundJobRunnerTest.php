<?php

namespace NorbyBaru\EasyRunner\Tests\Unit;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NorbyBaru\EasyRunner\BackgroundJobRunner;
use NorbyBaru\EasyRunner\Enum\PriorityEnum;
use NorbyBaru\EasyRunner\Enum\StatusEnum;
use NorbyBaru\EasyRunner\Tests\Fixture\DummyAction;
use NorbyBaru\EasyRunner\Tests\Fixture\InvalidDummyAction;
use NorbyBaru\EasyRunner\Tests\TestCase;
use RuntimeException;

class BackgroundJobRunnerTest extends TestCase
{
    protected BackgroundJobRunner $jobRunner;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('easypeasy-runner.allowed_namespaces', [DummyAction::class]);

        $this->jobRunner = resolve(BackgroundJobRunner::class);
    }

    public function test_basic_job_creation()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $jobId = $this->jobRunner->run(
            DummyAction::class,
            'execute',
            ['param1', 'param2']
        );

        $this->assertNotNull($jobId);
        $this->assertTrue(Str::isUuid($jobId));

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals(DummyAction::class, $job->class_name);
        $this->assertEquals('execute', $job->method_name);
        $this->assertEquals('["param1","param2"]', $job->parameters);
        $this->assertEquals(StatusEnum::PENDING->value, $job->status);
        $this->assertEquals(PriorityEnum::MEDIUM->value, $job->priority);
        $this->assertEquals($now->toDateTimeString(), $job->scheduled_at);
        $this->assertNull($job->started_at);
        $this->assertNull($job->completed_at);
        $this->assertNull($job->error);
        $this->assertEquals(0, $job->attempts);
        $this->assertEquals(config('easypeasy-runner.max_retries'), $job->max_attempts);
    }

    public function test_job_creation_with_priority_and_delay()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $jobId = $this->jobRunner->run(
            className: DummyAction::class,
            methodName: 'execute',
            params: ['param1', 'param2'],
            options: [
                'priority' => 'high',
                'delay' => 3600,
            ]
        );

        $job = DB::table('background_jobs')
            ->where('id', $jobId)
            ->first();

        $this->assertEquals(PriorityEnum::HIGH->value, $job->priority);
        $this->assertEquals(
            $now->addSeconds(3600)->toDateTimeString(),
            $job->scheduled_at
        );
    }

    public function test_it_should_throw_exception_on_invalid_class_validation()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class NonExistentClass does not exist');

        $this->jobRunner->run(
            'NonExistentClass',
            'handle',
            []
        );
    }

    public function test_it_should_throw_exception_on_invalid_method_validation()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Method nonexistentMethod does not exist');

        $this->jobRunner->run(
            DummyAction::class,
            'nonexistentMethod',
            []
        );
    }

    public function test_it_should_throw_exception_on_invalid_namespace()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Execution of class '.InvalidDummyAction::class.' is not permitted.');

        $this->jobRunner->run(
            InvalidDummyAction::class,
            'execute',
            ['param1', 'param2']
        );
    }
}
