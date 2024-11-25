<?php

namespace NorbyBaru\EasyRunner\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string run(string $className, string $methodName, array $params = [], array $options = [])
 */
class BackgroundJob extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'job-runner';
    }
}
