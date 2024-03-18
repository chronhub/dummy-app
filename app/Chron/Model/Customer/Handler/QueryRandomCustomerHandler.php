<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Query\QueryRandomCustomer;
use App\Chron\Projection\Provider\CustomerProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomCustomer::class,
)]
final readonly class QueryRandomCustomerHandler
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function __invoke(QueryRandomCustomer $query, Deferred $promise): void
    {
        $customer = $this->customerProvider->findRandomCustomer();

        $promise->resolve($customer);
    }
}
