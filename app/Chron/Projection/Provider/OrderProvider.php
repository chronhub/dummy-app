<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Order\OrderStatus;
use App\Chron\Projection\ReadModel\OrderReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use stdClass;

/**
 * @template TOrder of stdClass{id: string, customer_id: string, status: string, closed: int, created_at: string, balance: string}
 * @template TOrderItem of stdClass{id: string, customer_id: string, status: string, closed: int, created_at: string, balance: string, items: Collection<TItem>}
 * @template TItem of Collection{stdClass{id: string, order_id: string, sku_id: string, customer_id: string, quantity: int, unit_price:string}}
 */
final readonly class OrderProvider
{
    private array $pendingOrderStatuses;

    public function __construct(private Connection $connection)
    {
        $this->pendingOrderStatuses = [
            OrderStatus::CREATED->value,
            OrderStatus::MODIFIED->value,
        ];
    }

    /**
     * @return stdClass{TOrder}|null
     */
    public function findPendingOwnerOrder(string $customerId): ?stdClass
    {
        return $this->orderQuery()
            ->where('customer_id', $customerId)
            ->whereIn('status', $this->pendingOrderStatuses)
            ->first();
    }

    /**
     * @return stdClass{TOrderItem}|null
     */
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

    /**
     * Get orders view of a customer.
     *
     * @return Collection{stdClass{id: string, status: string, customer_id: string}}
     */
    public function getOrderSummaryOfCustomer(string $customerId): Collection
    {
        return $this->orderQuery()->select('id', 'status', 'customer_id')->where('customer_id', $customerId)->get();
    }

    /**
     * Get the summary of orders.
     *
     * @return stdClass{total_orders: int, total_balance: int, total_quantity: int}
     */
    public function getOrderSummary(): stdClass
    {
        return $this->orderQuery()
            ->selectRaw('count(*) as total_orders, SUM(balance::numeric) as total_balance, SUM(quantity) as total_quantity')
            ->whereIn('status', $this->pendingOrderStatuses)
            ->first();
    }

    /**
     * Find the current order of a customer ordered by created_at in descending order.
     *
     * @return stdClass{TOrderItem}|null
     */
    public function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        $order = $this->orderQuery()
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

    public function orderQuery(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER);
    }

    public function orderItemQuery(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER_ITEM);
    }
}
