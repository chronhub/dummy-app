<?php

declare(strict_types=1);

namespace App\Chron\Domain\Query;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Infra\CustomerRepository;
use React\Promise\Deferred;
use Storm\Reporter\ReportQuery;

#[AsMessageHandler(
    reporter: ReportQuery::class,
    fromTransport: 'sync',
    handles: GetOneRandomCustomer::class,
    method: '__invoke',
    priority: 0,
)]
final readonly class GetOneRandomCustomerHandler
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    public function __invoke(GetOneRandomCustomer $query, Deferred $promise): void
    {
        $promise->resolve($this->customerRepository->oneRandomCustomer());
    }
}
