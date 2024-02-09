<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class CustomerOrderProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function insert(CustomerId $customerId, OrderId $orderId, OrderStatus $status): void
    {
        $this->query()->insert([
            'customer_id' => $customerId->toString(),
            'order_id' => $orderId->toString(),
            'order_status' => $status->value,
        ]);
    }

    public function update(CustomerId $customerId, OrderId $orderId, OrderStatus $status): void
    {
        $this->query()
            ->where('customer_id', $customerId->toString())
            ->where('order_id', $orderId->toString())
            ->update([
                'order_status' => $status->value,
            ]);
    }

    public function findCurrentOrderOfCustomer(string $customerId): ?stdClass
    {
        return $this->query()
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function findRandomCustomerWithPendingOrder(): ?stdClass
    {
        return $this->query()
            ->inRandomOrder()
            ->whereIn('order_status', [OrderStatus::CREATED, OrderStatus::MODIFIED], 'or')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function query(): Builder
    {
        return $this->connection->table('read_customer_order');
    }
}
