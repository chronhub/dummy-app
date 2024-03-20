<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Projection\ReadModel\OrderReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenOrderPaid
{
    public function __construct(
        private OrderReadModel $orderReadModel,
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
    public function openCart(OrderPaid $event): void
    {
        $this->cartApplicationService->openCart($event->orderOwner()->toString());
    }
}
