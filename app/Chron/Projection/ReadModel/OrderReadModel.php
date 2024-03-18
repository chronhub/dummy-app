<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class OrderReadModel
{
    public const string TABLE_ORDER = 'read_order';

    public const string TABLE_ORDER_ITEM = 'read_order_item';

    public function __construct(private Connection $connection)
    {
    }

    public function insertOrder(string $orderId, string $orderOwner, string $status, string $balance, int $quantity): void
    {
        $this->queryOrder()->insert([
            'id' => $orderId,
            'customer_id' => $orderOwner,
            'status' => $status,
            'balance' => $balance,
            'quantity' => $quantity,
        ]);
    }

    public function insertOrderItems(string $orderId, string $orderOwner, array $items): void
    {
        $bulk = [];

        foreach ($items as $item) {
            $bulk[] = [
                'id' => $item['order_item_id'],
                'order_id' => $orderId,
                'customer_id' => $orderOwner,
                'sku_id' => $item['sku_id'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
            ];
        }

        $this->queryOrderItem()->insert($bulk);
    }

    public function updateOrderStatus(string $orderId, string $orderOwner, string $status): void
    {
        $this->queryOrder()
            ->where('id', $orderId)
            ->where('customer_id', $orderOwner)
            ->update([
                'status' => $status,
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
