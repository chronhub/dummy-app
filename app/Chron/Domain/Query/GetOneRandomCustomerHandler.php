<?php

declare(strict_types=1);

namespace App\Chron\Domain\Query;

use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use App\Chron\Infra\CustomerRepository;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: GetOneRandomCustomer::class,
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
