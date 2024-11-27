<?php

namespace NorbyBaru\EasyRunner\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use NorbyBaru\EasyRunner\BackgroundJobRepository;
use NorbyBaru\EasyRunner\EasyRunnerServiceProvider;
use NorbyBaru\EasyRunner\Tests\Fixture\DummyAction;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    use WithFaker;

    public BackgroundJobRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('easypeasy-runner.allowed_namespaces', [DummyAction::class]);
        Config::set('logging.channels.stderr', []);
        $this->repository = resolve(BackgroundJobRepository::class);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(base_path('migrations'));
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function getPackageProviders($app)
    {
        return [
            EasyRunnerServiceProvider::class,
        ];
    }
}
