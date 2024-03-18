<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Product\ProductStatus;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class CatalogReadModel
{
    final public const string TABLE = 'read_catalog';

    public function __construct(private Connection $connection)
    {
    }

    public function insert(string $skuId, string $skuCode, array $info, string $status): void
    {
        $this->query()->insert([
            'id' => $skuId,
            'sku_code' => $skuCode,
            'name' => $info['name'],
            'description' => $info['description'],
            'category' => $info['category'],
            'brand' => $info['brand'],
            'model' => $info['model'],
            'status' => $status,
        ]);
    }

    public function updateProductQuantityAndPrice(string $skuId, int $quantity, string $currentPrice): void
    {
        $this->query()
            ->where('id', $skuId)
            ->update([
                'quantity' => $quantity,
                'current_price' => $currentPrice,
            ]);
    }

    public function updateProductStatus(string $skuId, string $status): void
    {
        $this->query()
            ->where('id', $skuId)
            ->update(['status' => $status]);
    }

    public function updateReservation(string $skuId, int $quantity): void
    {
        $this->query()
            ->where('id', $skuId)
            ->update(['reserved' => $quantity]);
    }

    public function removeProductQuantity(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->decrement('quantity', $quantity);
    }

    public function findRandomAvailableProduct(): stdClass
    {
        return $this->query()
            ->where('status', ProductStatus::AVAILABLE->value)
            ->inRandomOrder()
            ->first();
    }

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
