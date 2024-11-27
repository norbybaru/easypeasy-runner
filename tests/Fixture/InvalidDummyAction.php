<?php

namespace NorbyBaru\EasyRunner\Tests\Fixture;

class InvalidDummyAction
{
    public function execute(string $param1, string $param2): string
    {
        return "Processed: {$param1}, {$param2}";
    }
}
