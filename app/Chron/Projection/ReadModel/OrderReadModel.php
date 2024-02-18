<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Storm\Contract\Clock\SystemClock;

final readonly class OrderReadModel
{
    public const TABLE_ORDER = 'read_order';

    public const TABLE_ORDER_ITEM = 'read_order_item';

    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function insertOrder(string $orderId, string $orderOwner, string $status): void
    {
        $this->queryOrder()->insert([
            'id' => $orderId,
            'customer_id' => $orderOwner,
            'status' => $status,
        ]);
    }

    public function updateOrder(string $orderId, string $orderOwner, string $balance, int $quantity, string $status): void
    {
        $this->queryOrder()
            ->where('id', $orderId)
            ->where('customer_id', $orderOwner)
            ->update(['quantity' => $quantity, 'balance' => $balance, 'status' => $status, 'updated_at' => $this->clock->generate()]);
    }

    public function insertOrderItem(string $orderItemId, string $orderId, string $orderOwner, string $skuId, int $quantity, string $unitPrice): void
    {
        $this->queryOrderItem()->insert([
            'id' => $orderItemId,
            'order_id' => $orderId,
            'customer_id' => $orderOwner,
            'sku_id' => $skuId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function deleteOrderItem(string $orderId, string $orderOwner): void
    {
        $this->queryOrderItem()
            ->where('order_id', $orderId)
            ->where('customer_id', $orderOwner)
            ->delete();
    }

    public function queryOrder(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER);
    }

    public function queryOrderItem(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER_ITEM);
    }
}
