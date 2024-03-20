<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Override;

use function abs;

final class InventoryReadModel extends ReadModelConnection
{
    final public const string TABLE = 'read_inventory';

    protected function insert(InventoryItemAdded $event): void
    {
        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'stock' => $event->totalStock()->value,
            'unit_price' => $event->unitPrice()->value,
            'reserved' => 0,
        ]);
    }

    protected function updateQuantity(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->update(['stock' => abs($quantity)]);
    }

    protected function updateReservation(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->update(['reserved' => abs($quantity)]);
    }

    #[Override]
    protected function up(): callable
    {
        return function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_price');
            $table->integer('stock');
            $table->integer('reserved')->default(0);

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        };
    }

    #[Override]
    protected function tableName(): string
    {
        return self::TABLE;
    }

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
