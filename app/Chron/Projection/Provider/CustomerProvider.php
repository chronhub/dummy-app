<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\CustomerReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class CustomerProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findRandomCustomer(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first(['id']);
    }

    private function query(): Builder
    {
        return $this->connection->table(CustomerReadModel::TABLE);
    }
}