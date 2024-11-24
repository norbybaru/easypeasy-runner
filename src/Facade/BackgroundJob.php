<?php

namespace NorbyBaru\EasyRunner\Facade;

use Illuminate\Support\Facades\Facade;
use NorbyBaru\EasyRunner\BackgroundJobRunner;

/**
 * @method static string run(string $className, string $methodName, array $params = [], int $retryAttempts = null)
 */
class BackgroundJob extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'job-runner';
    }
}