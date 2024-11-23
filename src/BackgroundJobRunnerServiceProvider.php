<?php

declare(strict_types=1);

namespace Norbybaru\EasypeasyRunner;

use Illuminate\Support\ServiceProvider;

class BackgroundJobRunnerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'easypeasy-job-runner');
        $this->mergeLoggingChannels();
    }

    public function boot()
    {
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
        return __DIR__.'/../config/easypeasy-job-runner.php';
    }

    protected function publishConfig()
    {
        $this->publishes([
            $this->configPath() => config_path('easypeasy-job-runner.php'),
        ], 'easypeasy-job-runner');
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