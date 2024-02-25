<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\CustomerReadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
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

    public function lastTenCustomers(): LazyCollection
    {
        return $this->query()->orderBy('created_at', 'desc')->take(10)->cursor();
    }

    public function getPaginatedCustomers(int $perPage): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, ['id', 'name', 'email', 'city', 'country', 'phone_number']);
    }

    private function query(): Builder
    {
        return $this->connection->table(CustomerReadModel::TABLE);
    }
}
