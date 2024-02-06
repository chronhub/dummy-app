<?php

declare(strict_types=1);

namespace App\Chron\EventPublisher;

use Storm\Contract\Message\DomainEvent;

interface MarshallEventPublisher
{
    public function publish(DomainEvent ...$events): void;
}
