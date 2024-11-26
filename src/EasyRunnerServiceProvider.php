<?php

declare(strict_types=1);

namespace NorbyBaru\EasyRunner;

use Illuminate\Support\ServiceProvider;
use NorbyBaru\EasyRunner\Console\BackgroundJobCleanupCommand;
use NorbyBaru\EasyRunner\Console\BackgroundJobExecutorCommand;
use NorbyBaru\EasyRunner\Console\BackgroundJobProcessorCommand;

class EasyRunnerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
        $this->publishDatabase();
        $this->registerCommands();
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'easypeasy-runner');
        $this->mergeLoggingChannels();

        $this->app->singleton(
            BackgroundJobRepository::class,
            fn ($app) => new BackgroundJobRepository(connection: $app['db']->connection())
        );

        $this->app->singleton(
            BackgroundJobRunner::class,
            fn () => new BackgroundJobRunner(
                repository: $this->app->make(BackgroundJobRepository::class),
            )
        );

        $this->app->alias(BackgroundJobRunner::class, 'job-runner');
    }

    protected function configPath(): string
    {
        return __DIR__.'/../config/easypeasy-runner.php';
    }

    protected function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => config_path('easypeasy-runner.php'),
            ], 'easypeasy-runner-config');
        }
    }

    protected function publishDatabase()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/create_background_jobs_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_background_jobs_table.php'),
        ], 'easypeasy-runner-migration');
    }

    protected function registerCommands()
    {
        $this->commands([
            BackgroundJobCleanupCommand::class,
            BackgroundJobExecutorCommand::class,
            BackgroundJobProcessorCommand::class,
        ]);
    }

    private function mergeLoggingChannels()
    {
        // This is the custom package logging configuration we just created earlier
        $packageConfig = require $this->configPath();

        $config = $this->app->make('config');

        // For now we manually merge in only the logging channels. We could also merge other logging config here as well if needed.
        // We do this merging manually since mergeConfigFrom() does not do a deep merge and we want to merge only the channels array
        $config->set('logging.channels', array_merge(
            $packageConfig['log_channel'] ?? [],
            $config->get('logging.channels', [])
        ));
    }

    public function provides()
    {
        return [BackgroundJobRunner::class];
    }
}
