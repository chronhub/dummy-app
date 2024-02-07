<?php

declare(strict_types=1);

namespace App\Chron\Package\Aggregate\Contract;

use Generator;
use Storm\Contract\Message\DomainEvent;

interface AggregateRoot
{
    /**
     * @param Generator<DomainEvent> $events
     */
    public static function reconstitute(AggregateIdentity $aggregateId, Generator $events): ?static;

    /**
     * @return array<DomainEvent>|array
     */
    public function releaseEvents(): array;

    public function identity(): AggregateIdentity;

    /**
     * @return positive-int
     */
    public function version(): int;
}
