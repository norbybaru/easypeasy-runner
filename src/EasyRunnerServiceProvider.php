<?php

namespace NorbyBaru\EasyRunner;

use Illuminate\Support\ServiceProvider;
use NorbyBaru\EasyRunner\Console\BackgroundProcessCommand;

class EasyRunnerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'easypeasy-runner');
        $this->mergeLoggingChannels();

        $this->app->singleton(BackgroundJobRunner::class, function ($app) {
            return new BackgroundJobRunner(config: $app['config']['easypeasy-runner']);
        });
    }

    public function boot()
    {
        $this->commands([
            BackgroundProcessCommand::class
        ]);

        // Configure logging channels
        // Config::set('logging.channels.background_jobs', [
        //     'driver' => 'daily',
        //     'path' => Storage::path('logs/background_jobs.log'),
        //     'level' => 'info',
        // ]);

        // Config::set('logging.channels.background_jobs_errors', [
        //     'driver' => 'daily',
        //     'path' => Storage::path('logs/background_jobs_errors.log'),
        //     'level' => 'error',
        // ]);
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
}