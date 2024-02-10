<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderRefunded;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

use function sprintf;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderRefunded::class,
)]
final class WhenOrderRefunded
{
    public function __invoke(OrderRefunded $event): void
    {
        logger(sprintf('Order refunded: %s for customer: %s with balance: %s',
            $event->orderId()->toString(),
            $event->customerId()->toString(),
            $event->balance()->value()
        ));
    }
}
