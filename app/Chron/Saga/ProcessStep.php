<?php

declare(strict_types=1);

namespace App\Chron\Saga;

use Storm\Contract\Message\Messaging;
use Throwable;

interface ProcessStep
{
    public function shouldHandle(Messaging $event): bool;

    public function handle(Messaging $event): void;

    public function compensate(?Throwable $exception): void;
}
