<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Order\Event\OrderCreated;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

final class OrderItemReadModel extends ReadModelConnection
{
    public const string TABLE_ORDER_ITEM = 'read_order_item';

    public function insert(OrderCreated $event): void
    {
        $items = $event->orderItems()->toArray();

        $bulk = [];

        foreach ($items as $item) {
            $bulk[] = [
                'id' => $item['order_item_id'],
                'order_id' => $event->aggregateId()->toString(),
                'customer_id' => $event->orderOwner()->toString(),
                'sku_id' => $item['sku_id'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
            ];
        }

        $this->query()->insert($bulk);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('sku_id');
            $table->uuid('customer_id');
            $table->unsignedInteger('quantity');
            $table->string('unit_price');

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        };
    }

    protected function tableName(): string
    {
        return self::TABLE_ORDER_ITEM;
    }

    public function query(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER_ITEM);
    }
}
