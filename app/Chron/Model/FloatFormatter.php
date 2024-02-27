<?php

declare(strict_types=1);

namespace App\Chron\Model;

use function is_float;
use function number_format;

final class FloatFormatter
{
    public function zero(): string
    {
        return '0.00';
    }

    public static function toFloat(string|float|int $value): float
    {
        return (float) self::format($value);
    }

    public static function toString(string|float|int $value): string
    {
        return self::format($value);
    }

    private static function format(string|float|int $value): string
    {
        $floatValue = is_float($value) ? $value : (float) $value;

        return number_format($floatValue, 2, '.', '');
    }
}
