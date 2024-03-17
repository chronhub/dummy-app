<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\CustomerReadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use stdClass;

/**
 * @template TCustomer of object{
 *     id: string, name: string, email: string,
 *     birthdy: string, gender: string, phone_number: string,
 *     street:string, city: string, postal_code: string, country: string,
 *     created_at: string, updated_at: string|null}
 */
final readonly class CustomerProvider
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return object{TCustomer}|null
     */
    public function findCustomerById(string $customerId): ?stdClass
    {
        return $this->query()->find($customerId);
    }

    /**
     * @return object{id: string}|null
     */
    public function findRandomCustomer(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first(['id']);
    }

    /**
     * @return LazyCollection<TCustomer>
     */
    public function lastTenCustomers(): LazyCollection
    {
        return $this->query()->orderBy('created_at', 'desc')->take(10)->cursor();
    }

    public function getPaginatedCustomers(int $perPage): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, ['id', 'name', 'email', 'city', 'country']);
    }

    private function query(): Builder
    {
        return $this->connection->table(CustomerReadModel::TABLE);
    }
}
