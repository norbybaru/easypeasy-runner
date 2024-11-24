<?php

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Facade\BackgroundJob;

if (! function_exists('\NorbyBaru\EasyRunner\runBackgroundJob')) {
    /**
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param integer|null $retryAttempts
     * @return string
     */
    function runBackgroundJob(
        string $className,
        string $methodName,
        array $params = [],
        int $retryAttempts = null
    ): string {
        return BackgroundJob::run(
            className: $className,
            methodName: $methodName,
            params: $params,
            retryAttempts: $retryAttempts
        );
    }
}
