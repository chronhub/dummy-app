<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Inventory\Event\InventoryItemAdded;
use App\Chron\Model\Order\ItemCollection;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Product\Event\ProductCreated;
use App\Chron\Model\Product\ProductStatus;
use Illuminate\Database\Schema\Blueprint;

use function abs;

final class CatalogReadModel extends ReadModelConnection
{
    final public const string TABLE = 'read_catalog';

    public function insert(ProductCreated $event): void
    {
        $info = $event->productInfo()->toArray();

        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'sku_code' => $event->skuCode(),
            'name' => $info['name'],
            'description' => $info['description'],
            'category' => $info['category'],
            'brand' => $info['brand'],
            'model' => $info['model'],
            'status' => $event->productStatus()->value,
        ]);
    }

    protected function updateProductQuantityAndPrice(InventoryItemAdded $event): void
    {
        $this->query()
            ->where('id', $event->aggregateId()->toString())
            ->increment('quantity', $event->totalStock()->value,
                ['current_price' => $event->unitPrice()->value]
            );

    }

    protected function updateProductStatus(string $skuId, string $status): void
    {
        $this->query()->where('id', $skuId)->update(['status' => $status]);
    }

    protected function incrementReservation(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->increment('reserved', abs($quantity));
    }

    protected function decrementReservation(string $skuId, int $quantity): void
    {
        $this->query()->where('id', $skuId)->decrement('reserved', abs($quantity));
    }

    protected function removeProductQuantity(ItemCollection $orderItems): void
    {
        $orderItems->getItems()->each(function (OrderItem $orderItem): void {
            $this->query()
                ->where('id', $orderItem->skuId->toString())
                ->decrement('quantity', $orderItem->quantity->value);
        });
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->text('sku_code')->unique();
            $table->string('name');
            $table->string('description');
            $table->string('category');
            $table->string('brand');
            $table->string('model');
            $table->enum('status', ProductStatus::toStrings());
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved')->default(0);
            $table->string('current_price')->nullable();
            $table->string('old_price')->nullable();

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrent();
        };
    }

    protected function tableName(): string
    {
        return self::TABLE;
    }
}
