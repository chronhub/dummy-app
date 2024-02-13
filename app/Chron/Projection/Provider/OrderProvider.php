<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Order\OrderStatus;
use App\Chron\Projection\ReadModel\OrderReadModel;
use DateInterval;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use stdClass;
use Storm\Contract\Clock\SystemClock;

final readonly class OrderProvider
{
    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function findPendingOrders(): LazyCollection
    {
        return $this->query()
            ->whereIn('order_status', [OrderStatus::CREATED->value, OrderStatus::MODIFIED])
            ->where('closed', 0)
            ->cursor();
    }

    public function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        return $this->query()
            ->where('read_order.customer_id', $customerId)
            ->join('read_order_item', 'read_order.id', '=', 'read_order_item.order_id')
            ->orderBy('read_order.created_at', 'desc')
            ->where('read_order.closed', 0)
            ->first();
    }

    public function findOrderOfCustomer(string $customerId, string $orderId): ?stdClass
    {
        return $this->query()
            ->find($orderId)
            ->where('customer_id', $customerId)
            ->where('closed', 0)
            ->first();
    }

    public function findOrdersByStatus(OrderStatus $status, int $limit = 500): LazyCollection
    {
        return $this->query()
            ->where('order_status', $status->value)
            ->where('closed', 0)
            ->limit($limit)
            ->cursor();
    }

    public function findCancelledOrRefundedOrders(): LazyCollection
    {
        return $this->query()
            ->whereIn('order_status', [OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value])
            ->where('closed', 0)
            ->cursor();
    }

    public function findOverdueDeliveredOrders(): LazyCollection
    {
        return $this->query()
            ->where('order_status', OrderStatus::DELIVERED->value)
            ->where('closed', 0)
            ->where('created_at', '<', $this->clock->now()->sub(new DateInterval('PT5M')))
            ->cursor();
    }

    public function query(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER);
    }
}
