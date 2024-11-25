<?php

namespace NorbyBaru\EasyRunner\Enum;

enum PriorityEnum: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
