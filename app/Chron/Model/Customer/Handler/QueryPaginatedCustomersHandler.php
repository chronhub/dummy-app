<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Query\QueryPaginatedCustomers;
use App\Chron\Projection\Provider\CustomerProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryPaginatedCustomers::class,
)]
final readonly class QueryPaginatedCustomersHandler
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function __invoke(QueryPaginatedCustomers $query, Deferred $promise): void
    {
        $customers = $this->customerProvider->getPaginatedCustomers($query->perPage);

        $promise->resolve($customers);
    }
}
