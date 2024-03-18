<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Query\QueryCustomerProfile;
use App\Chron\Projection\Provider\CustomerProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryCustomerProfile::class,
)]
final readonly class QueryCustomerProfileHandler
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function __invoke(QueryCustomerProfile $query, Deferred $promise): void
    {
        $customer = $this->customerProvider->findCustomerById($query->customerId()->toString());

        $promise->resolve($customer);
    }
}
