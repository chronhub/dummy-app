<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\InventoryReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use stdClass;

final readonly class InventoryProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findInventoryById(string $skuId): ?stdClass
    {
        return $this->query()->find($skuId);
    }

    public function findRandomItem(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first();
    }

    public function findRandomItems(int $limit = 10): LazyCollection
    {
        return $this->query()->inRandomOrder()->limit($limit)->cursor();
    }

    public function getInventorySummary(): stdClass
    {
        return $this->query()
            ->selectRaw('count(*) as total_items, SUM(stock) as total_stock ,SUM(reserved) as total_reserved')
            ->first();
    }

    private function query(): Builder
    {
        return $this->connection->table(InventoryReadModel::TABLE);
    }
}
