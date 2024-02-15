<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Storm\Contract\Clock\SystemClock;

use function abs;

final readonly class InventoryReadModel
{
    final public const TABLE = 'read_inventory';

    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function insert(string $skuId, int $stock, string $unitPrice): void
    {
        $this->query()->insert([
            'id' => $skuId,
            'stock' => $stock,
            'unit_price' => $unitPrice,
            'reserved' => 0,
            'created_at' => $this->clock->generate(),
        ]);
    }

    public function updateQuantity(string $skuId, int $quantity): void
    {
        $this->query()
            ->where('id', $skuId)
            ->update([
                'stock' => $quantity,
                'updated_at' => $this->clock->generate(),
            ]);
    }

    public function increment(string $skuId, int $quantity): void
    {
        $this->query()
            ->where('id', $skuId)
            ->increment('reserved', abs($quantity), $this->updateTime());
    }

    public function decrement(string $skuId, int $quantity): void
    {
        $this->query()
            ->where('id', $skuId)
            ->decrement('reserved', abs($quantity), $this->updateTime());
    }

    public function getAvailableProductQuantity(string $skuId): int
    {
        return $this->query()
            ->selectRaw('SUM(stock - reserved) as available')
            ->where('id', $skuId)
            ->value('available') ?? 0;
    }

    private function updateTime(): array
    {
        return ['updated_at' => $this->clock->generate()];
    }

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
