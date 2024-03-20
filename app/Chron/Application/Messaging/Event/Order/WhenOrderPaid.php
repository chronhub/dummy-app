<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Order\Event\OrderPaid;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenOrderPaid
{
    public function __construct(private CartApplicationService $cartApplicationService)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderPaid::class,
    )]
    public function openCart(OrderPaid $event): void
    {
        $this->cartApplicationService->openCart($event->orderOwner()->toString());
    }
}
