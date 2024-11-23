<?php

use Illuminate\Support\Facades\Storage;

return [
    'max_retries' => 3,
    'retry_delay' => 5,

    /**
     * Allowed namespaces for background job execution. eg. 'App\\Jobs\\', 'App\\Services\\'
     */
    'allowed_namespaces' => [],

    'log_channel' => [
        'background_jobs' => [
            'driver' => 'daily',
            'path' => Storage::path('logs/background_jobs.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'background_jobs_errors' => [
            'driver' => 'daily',
            'path' => Storage::path('logs/background_jobs_errors.log'),
            'level' => 'error',
            'days' => 7,
        ],
    ],
];