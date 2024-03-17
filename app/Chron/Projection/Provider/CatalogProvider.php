<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Product\ProductStatus;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;

/**
 * @template TCatalog of object{
 *      id: string, sku_code: string, name: string, description: string, category: string, brand: string, model: string,
 *      quantity: int, reserved: int, current_price: string, old_price: string|null,
 *      status: string,
 *      created_at: string,
 *      updated_at: string|null
 * }
 */
final readonly class CatalogProvider
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return LazyCollection{TCatalog}
     */
    public function getAvailableProducts(int $limit): LazyCollection
    {
        return $this->query()
            ->where('status', ProductStatus::AVAILABLE->value)
            ->limit($limit)
            ->cursor();
    }

    public function getPaginatedProducts(int $perPage): LengthAwarePaginator
    {
        return $this->query()->where('status')->paginate($perPage);
    }

    private function query(): Builder
    {
        return $this->connection->table(CatalogReadModel::TABLE);
    }
}
