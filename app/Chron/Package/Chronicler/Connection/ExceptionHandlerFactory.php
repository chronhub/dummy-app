<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Connection;

use RuntimeException;

class ExceptionHandlerFactory
{
    public static function make(string $connectionName): PgsqlExceptionHandler
    {
        return match ($connectionName) {
            'pgsql' => new PgsqlExceptionHandler(),
            default => throw new RuntimeException('No exception handler for connection '.$connectionName)
        };
    }
}
