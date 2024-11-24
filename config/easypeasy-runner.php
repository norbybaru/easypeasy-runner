<?php

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
            'path' => storage_path('logs/background_jobs.log'),
            'level' => 'debug',
            'days' => 14,
        ],
        'background_jobs_errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/background_jobs_errors.log'),
            'level' => 'error',
            'days' => 14,
        ],
    ],
];
