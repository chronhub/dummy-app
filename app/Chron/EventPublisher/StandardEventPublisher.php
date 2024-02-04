<?php

declare(strict_types=1);

namespace App\Chron\EventPublisher;

use App\Chron\Reporter\ReportEvent;
use Storm\Contract\Message\DomainEvent;

class StandardEventPublisher
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
