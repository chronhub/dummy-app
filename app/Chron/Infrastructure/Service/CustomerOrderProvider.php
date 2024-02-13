<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Balance;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use DateInterval;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use stdClass;
use Storm\Contract\Clock\SystemClock;

final readonly class CustomerOrderProvider
{
    public const TABLE = 'read_customer_order';

    public function __construct(
        private Connection $connection,
        private SystemClock $clock
    ) {
    }

    public function insert(CustomerId $customerId, OrderId $orderId, OrderStatus $status): void
    {
        $this->query()->insert([
            'customer_id' => $customerId->toString(),
            'order_id' => $orderId->toString(),
            'order_status' => $status->value,
        ]);
    }

    public function update(CustomerId $customerId, OrderId $orderId, OrderStatus $status, ?Balance $balance = null): void
    {
        $update = ['order_status' => $status->value];

        if ($balance !== null) {
            $update['balance'] = $balance->value();
        }

        $this->query()
            ->where('customer_id', $customerId->toString())
            ->where('order_id', $orderId->toString())
            ->where('closed', 0)
            ->update($update);
    }

    public function close(CustomerId $customerId, OrderId $orderId, OrderStatus $status, string $reason): void
    {
        $this->query()
            ->where('customer_id', $customerId->toString())
            ->where('order_id', $orderId->toString())
            ->update([
                'order_status' => $status->value,
                'closed' => 1,
                'closed_at' => $this->clock->generate(),
                'reason' => $reason,
            ]);
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
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->where('closed', 0)
            ->first();
    }

    public function findOrderOfCustomer(string $customerId, string $orderId): ?stdClass
    {
        return $this->query()
            ->where('customer_id', $customerId)
            ->where('order_id', $orderId)
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

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
