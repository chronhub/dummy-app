<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Repository;

use App\Chron\Aggregate\Contract\AggregateRepository;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerId;

final readonly class CustomerChroniclerRepository implements CustomerCollection
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(CustomerId $customerId): ?Customer
    {
        return $this->repository->retrieve($customerId);
    }

    public function save(Customer $customer): void
    {
        $this->repository->store($customer);
    }
}
