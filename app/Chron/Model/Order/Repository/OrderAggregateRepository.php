<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Repository;

use App\Chron\Aggregate\Contract\AggregateRepository;
use App\Chron\Aggregate\Contract\AggregateRoot;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;

final readonly class OrderAggregateRepository implements OrderList
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(OrderId $orderId): ?Order
    {
        /** @var AggregateRoot&Order $order */
        $order = $this->repository->retrieve($orderId);

        return $order;
    }

    public function save(Order $order): void
    {
        $this->repository->store($order);
    }
}
