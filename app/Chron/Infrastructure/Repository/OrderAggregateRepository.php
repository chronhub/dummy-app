<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Aggregate\Contract\AggregateRepository;
use App\Chron\Package\Aggregate\Contract\AggregateRoot;
use App\Chron\Package\Attribute\AggregateRepository\AsAggregateRepository;

#[AsAggregateRepository(
    chronicler: 'chronicler.event.transactional.standard.pgsql',
    streamName: 'order',
    aggregateRoot: Order::class
)]
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
