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

    public function queryOrder(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER);
    }

    public function queryOrderItem(): Builder
    {
        return $this->connection->table(self::TABLE_ORDER_ITEM);
    }
}
