<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Connection;

use Storm\Stream\StreamName;
use Throwable;

trait ExceptionHandlerTrait
{
    protected function handleException(Throwable $exception, StreamName $streamName): void
    {
        $factory = ExceptionHandlerFactory::make($this->connectionName());

        $factory->handle($exception, $streamName);
    }

    abstract protected function connectionName(): string;
}
