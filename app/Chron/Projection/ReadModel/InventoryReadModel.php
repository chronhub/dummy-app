<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use Illuminate\Database\Schema\Blueprint;

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

    protected function decrementQuantity(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->decrement('stock', abs($quantity));
    }

    protected function incrementReservation(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->increment('reserved', abs($quantity));
    }

    protected function decrementReservation(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->decrement('reserved', abs($quantity));
    }

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

    protected function tableName(): string
    {
        return self::TABLE;
    }
}
