<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Connection;

use App\Chron\Package\Chronicler\Exception\ConnectionConcurrencyFailure;
use App\Chron\Package\Chronicler\Exception\ConnectionQueryFailure;
use Illuminate\Database\QueryException;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Stream\StreamName;
use Throwable;

final readonly class PgsqlExceptionHandler
{
    public function handle(Throwable $exception, StreamName $streamName): void
    {
        // todo all exceptions must be caught by the rollback transaction
        if (! $exception instanceof QueryException) {
            throw $exception;
        }

        match ($exception->getCode()) {
            '42P01' => throw StreamNotFound::withStreamName($streamName),
            '23000', '23505' => throw new ConnectionConcurrencyFailure($exception->getMessage(), (int) $exception->getCode(), $exception),
            default => throw new ConnectionQueryFailure($exception->getMessage(), (int) $exception->getCode(), $exception)
        };
    }
}
