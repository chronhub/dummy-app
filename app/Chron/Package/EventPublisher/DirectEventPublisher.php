<?php

declare(strict_types=1);

namespace App\Chron\Package\EventPublisher;

use App\Chron\Package\Reporter\ReportEvent;
use Storm\Contract\Message\DomainEvent;

class DirectEventPublisher implements MarshallEventPublisher
{
    public function __construct(protected ReportEvent $reporter)
    {
    }

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->reporter->relay($event);
        }
    }
}
