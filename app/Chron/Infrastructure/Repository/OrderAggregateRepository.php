<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Repository;

use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use Generator;
use Storm\Aggregate\Attribute\AsAggregateRepository;
use Storm\Contract\Aggregate\AggregateRepository;
use Storm\Contract\Aggregate\AggregateRoot;

#[AsAggregateRepository(
    chronicler: 'chronicler.event.transactional.standard.pgsql',
    streamName: 'order',
    aggregateRoot: Order::class,
    messageDecorator: 'event.decorator.chain.default'
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

    public function history(OrderId $orderId): Generator
    {
        return $this->repository->retrieveHistory($orderId, null);
    }
}
