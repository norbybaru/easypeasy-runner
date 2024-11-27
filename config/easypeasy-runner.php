<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Maximum number of retries for a failed job
    |
    */
    'max_retries' => 1,

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | Delay in seconds before retrying a failed job
    |
    */
    'retry_delay' => 1,

    /*
    |--------------------------------------------------------------------------
    | Allowed Namespaces Whitelist
    |--------------------------------------------------------------------------
    |
    | Allowed namespaces for background job execution. eg. 'App\\Jobs\\', 'App\\Services\\'
    |
    */
    'allowed_namespaces' => [
        'App\\',
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | Log channel configuration for background jobs
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Default Priority
    |--------------------------------------------------------------------------
    |
    | Set Default priority for background jobs.
    |
    | Possible values: 'low', 'medium', 'high'
    |
    */
    'default_priority' => 'medium',
];
