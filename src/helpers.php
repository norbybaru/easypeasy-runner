<?php

namespace NorbyBaru\EasyRunner;

use NorbyBaru\EasyRunner\Facade\BackgroundJob;

if (! function_exists('\NorbyBaru\EasyRunner\runBackgroundJob')) {
    function runBackgroundJob(
        string $className,
        string $methodName,
        array $parameters = [],
        array $options = [],
    ): string {
        return BackgroundJob::run(
            className: $className,
            methodName: $methodName,
            params: $parameters,
            options: $options
        );
    }
}
