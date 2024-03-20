<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Order\OrderStatus;
use App\Chron\Projection\ReadModel\OrderItemReadModel;
use App\Chron\Projection\ReadModel\OrderReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use stdClass;

/**
 * @template TOrder of object{
 *     id: string, customer_id: string,
 *     status: string,
 *     closed: int,
 *     balance: string,
 *     quantity: int,
 *     created_at: string,
 *     updated_at: string|null
 * }
 * @template TOrderItems of object{
 *     TOrder,
 *     items: Collection<TOrderItem>|null,
 * }
 * @template TOrderItem of object{
 *     id: string,
 *     order_id: string,
 *     sku_id: string,
 *     customer_id: string,
 *     quantity: int,
 *     unit_price:string
 * }
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
     * @return object{TOrder}|null
     */
    public function findPendingOwnerOrder(string $customerId): ?stdClass
    {
        return $this->orderQuery()
            ->where('customer_id', $customerId)
            ->whereIn('status', $this->pendingOrderStatuses)
            ->first();
    }

    /**
     * @return object{TOrder}|null
     */
    public function findRandomPendingOwnerOrder(): ?stdClass
    {
        return $this->orderQuery()
            ->whereIn('status', $this->pendingOrderStatuses)
            ->inRandomOrder()
            ->first();
    }

    /**
     * @return object{TOrderItems}|null
     */
    public function findOrderOfCustomer(string $customerId, string $orderId): ?stdClass
    {
        $order = $this->orderQuery()
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
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
     * @return Collection{ object{id: string, status: string, customer_id: string}}
     */
    public function getOrderSummaryOfCustomer(string $customerId): Collection
    {
        return $this->orderQuery()
            ->select('id', 'status', 'customer_id', 'created_at')
            ->where('customer_id', $customerId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get the summary of orders.
     *
     * @return object{total_orders: int, total_balance: int, total_quantity: int}
     */
    public function getOrderSummary(): stdClass
    {
        return $this->orderQuery()
            ->selectRaw('COALESCE(count(*), 0) as total_orders, COALESCE(SUM(balance::numeric), 0) as total_balance, COALESCE(SUM(quantity), 0) as total_quantity')
            ->whereIn('status', $this->pendingOrderStatuses)
            ->first();
    }

    /**
     * @return object{TOrder}|null
     */
    public function findOpenOrderOfCustomer(string $customerId): ?stdClass
    {
        return $this->orderQuery()
            ->where('customer_id', $customerId)
            ->whereIn('status', [OrderStatus::CREATED->value])
            ->first();
    }

    public function orderQuery(): Builder
    {
        return $this->connection->table(OrderReadModel::TABLE_ORDER);
    }

    public function orderItemQuery(): Builder
    {
        return $this->connection->table(OrderItemReadModel::TABLE_ORDER_ITEM);
    }
}
