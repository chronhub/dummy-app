<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\InventoryReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use stdClass;

/**
 * @template TInventory of object{
 *     id: string,
 *     stock: int, reserved: int, unit_price: string,
 *     created_at: string, updated_at: string|null
 * }
 */
final readonly class InventoryProvider
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return stdClass{TInventory}|null
     */
    public function findInventoryById(string $skuId): ?stdClass
    {
        return $this->query()->find($skuId);
    }

    /**
     * @return stdClass{TInventory}|null
     */
    public function findRandomItem(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first();
    }

    /**
     * @return LazyCollection<TInventory>
     */
    public function getFirstTenItems(): LazyCollection
    {
        return $this->query()->limit(10)->orderBy('created_at')->cursor();
    }

    /**
     * @return stdClass{total_items: int, total_stock: int, total_reserved: int}
     */
    public function getInventorySummary(): stdClass
    {
        return $this->query()
            ->selectRaw('count(*) as total_items, SUM(stock) as total_stock, SUM(reserved) as total_reserved')
            ->first();
    }

    public function getAvailableProductQuantity(string $skuId): int
    {
        return $this->query()
            ->selectRaw('SUM(stock - reserved) as available')
            ->where('id', $skuId)
            ->value('available') ?? 0;
    }

    private function query(): Builder
    {
        return $this->connection->table(InventoryReadModel::TABLE);
    }
}
