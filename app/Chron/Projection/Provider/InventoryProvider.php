<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\InventoryReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use stdClass;

final readonly class InventoryProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findRandomItem(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first();
    }

    public function findRandomItems(int $limit = 10): Collection
    {
        return $this->query()->inRandomOrder()->limit($limit)->get();
    }

    private function query(): Builder
    {
        return $this->connection->table(InventoryReadModel::TABLE);
    }
}
