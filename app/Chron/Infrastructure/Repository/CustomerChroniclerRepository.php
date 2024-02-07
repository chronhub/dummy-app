<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Package\Aggregate\Contract\AggregateRepository;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;

final readonly class CustomerChroniclerRepository implements CustomerCollection
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(CustomerId $customerId): ?Customer
    {
        /** @var AggregateRoot&Customer $aggregate */
        $aggregate = $this->repository->retrieve($customerId);

        return $aggregate;
    }

    public function save(Customer $customer): void
    {
        $this->repository->store($customer);
    }
}
