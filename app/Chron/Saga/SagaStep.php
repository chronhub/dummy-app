<?php

declare(strict_types=1);

namespace App\Chron\Saga;

use Storm\Contract\Message\Messaging;
use Throwable;

interface SagaStep
{
    public function shouldHandle(Messaging $event): bool;

    public function handle(Messaging $event): void;

    public function compensate(Messaging $event, ?Throwable $exception): void;
}
