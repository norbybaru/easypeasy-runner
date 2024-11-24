<?php

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Facade\BackgroundJob;

if (! function_exists('\NorbyBaru\EasyRunner\runBackgroundJob')) {
    function runBackgroundJob(
        string $className,
        string $methodName,
        array $params = [],
        ?int $retryAttempts = null
    ): string {
        return BackgroundJob::run(
            className: $className,
            methodName: $methodName,
            params: $params,
            retryAttempts: $retryAttempts
        );
    }
}
