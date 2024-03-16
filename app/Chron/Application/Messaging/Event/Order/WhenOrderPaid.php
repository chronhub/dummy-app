<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CartReadModel;
use App\Chron\Projection\ReadModel\OrderReadModel;

final readonly class WhenOrderPaid
{
    public function __construct(
        private OrderReadModel $orderReadModel,
        private CartReadModel $cartReadModel,
        private CartApplicationService $cartApplicationService,
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderPaid::class,
        priority: 0
    )]
    public function updateOrderStatus(OrderPaid $event): void
    {
        $this->orderReadModel->updateOrderStatus(
            $event->aggregateId()->toString(),
            $event->orderOwner()->toString(),
            $event->orderStatus()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderPaid::class,
        priority: 1
    )]
    public function deleteCart(OrderCreated $event): void
    {
        $this->cartReadModel->deleteCart($event->orderOwner()->toString());
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderPaid::class,
        priority: 2
    )]
    public function openCart(OrderPaid $event): void
    {
        $this->cartApplicationService->openCart($event->orderOwner()->toString());
    }
}
