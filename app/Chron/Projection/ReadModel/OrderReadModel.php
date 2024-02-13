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

    public function insertOrder(string $customerId, string $orderId, string $status): void
    {
        $this->queryOrder()->insert([
            'id' => $orderId,
            'customer_id' => $customerId,
            'order_status' => $status,
        ]);
    }

    public function updateOrder(string $customerId, string $orderId, string $balance, int $quantity): void
    {
        $this->queryOrder()
            ->find($orderId)
            ->where('customer_id', $customerId)
            ->update(['quantity' => $quantity, 'balance' => $balance, 'updated_at' => $this->clock->generate()]);
    }

    public function insertOrderItem(string $orderItemId, string $orderId, string $customerId, string $itemId, string $skuId, string $quantity, string $unitPrice): void
    {
        $this->queryOrderItem()->insert([
            'id' => $orderItemId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'item_id' => $itemId,
            'sku_id' => $skuId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
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
