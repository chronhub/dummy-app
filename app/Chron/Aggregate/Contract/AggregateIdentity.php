<?php

declare(strict_types=1);

namespace App\Chron\Aggregate\Contract;

interface AggregateIdentity
{
    public static function fromString(string $aggregateId): static;

    public function toString(): string;

    public function equalsTo(self $aggregateId): bool;
}
