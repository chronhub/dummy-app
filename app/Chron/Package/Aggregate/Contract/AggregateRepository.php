<?php

declare(strict_types=1);

namespace App\Chron\Package\Aggregate\Contract;

use App\Chron\Package\Chronicler\Contracts\QueryFilter;
use Generator;

interface AggregateRepository
{
    public function retrieve(AggregateIdentity $aggregateId): ?AggregateRoot;

    public function store(AggregateRoot $aggregateRoot): void;

    public function retrieveFiltered(AggregateIdentity $aggregateId, QueryFilter $queryFilter): ?AggregateRoot;

    public function retrieveHistory(AggregateIdentity $aggregateId, ?QueryFilter $queryFilter): Generator;
}