<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Order\OrderStatus;
use App\Chron\Projection\ReadModel\OrderReadModel;
use DateInterval;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass;
use Storm\Contract\Clock\SystemClock;

/**
 * @template TItem of Collection{stdClass{id: string, order_id: string, sku_id: string, customer_id: string, quantity: int, unit_price:string}}
 */
final readonly class OrderProvider
{
    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function findPendingOrders(): LazyCollection
    {
        return $this->orderQuery()
            ->whereIn('order_status', [OrderStatus::CREATED->value, OrderStatus::MODIFIED])
            ->where('closed', 0)
            ->cursor();
    }

    public function findOrderOfCustomer(string $customerId, string $orderId): ?stdClass
    {
        $order = $this->orderQuery()
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $order) {
            return null;
        }

        $orderItems = $this->orderItemQuery()->where('order_id', $order->id)->get();

        $order->items = $orderItems;

        return $order;
    }

    public function getOrderSummaryOfCustomer(string $customerId): Collection
    {
        return $this->orderQuery()->select('id', 'status', 'customer_id')->where('customer_id', $customerId)->get();
    }

    public function getOrderSummary(): stdClass
    {
        return $this->orderQuery()
            ->selectRaw('count(*) as total_orders, SUM(balance::numeric) as total_balance, SUM(quantity) as total_quantity')
            ->whereIn('status', [OrderStatus::CREATED->value, OrderStatus::MODIFIED])
            ->first();
    }

    public function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        $order = $this->orderQuery()
            ->where('customer_id', $customerId)
            ->where('closed', 0) // todo removed and use the order status
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $order) {
            return null;
        }

        $orderItems = $this->orderItemQuery()->where('order_id', $order->id)->get();

        $order->items = $orderItems;

        return $order;
    }

    public function findOrdersByStatus(OrderStatus $status, int $limit = 500): LazyCollection
    {
        return $this->orderQuery()
            ->where('order_status', $status->value)
            ->where('closed', 0)
            ->limit($limit)
            ->cursor();
    }

    public function findCancelledOrRefundedOrders(): LazyCollection
    {
        return $this->orderQuery()
            ->whereIn('order_status', [OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value])
            ->where('closed', 0)
            ->cursor();
    }

    public function findOverdueDeliveredOrders(): LazyCollection
    {
        return $this->orderQuery()
            ->where('order_status', OrderStatus::DELIVERED->value)
            ->where('closed', 0)
            ->where('created_at', '<', $this->clock->now()->sub(new DateInterval('PT5M')))
            ->cursor();
    }

    public function orderQuery(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER);
    }

    public function orderItemQuery(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER_ITEM);
    }
}
