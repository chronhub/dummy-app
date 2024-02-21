<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\CustomerReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use stdClass;

final readonly class CustomerProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findCustomerById(string $customerId): ?stdClass
    {
        return $this->query()->find($customerId);
    }

    public function findRandomCustomer(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first(['id']);
    }

    public function getPaginatedCustomers(int $page, int $perPage): Collection
    {
        return $this->query()->forPage($page, $perPage)->get(['id', 'name', 'email', 'city', 'country']);
    }

    private function query(): Builder
    {
        return $this->connection->table(CustomerReadModel::TABLE);
    }
}
