<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

use function sprintf;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderModified::class,
)]
final class WhenOrderModified
{
    public function __invoke(OrderModified $event): void
    {
        logger(sprintf('Order modified: %s for customer: %s with amount: %s',
            $event->orderId()->toString(),
            $event->customerId()->toString(),
            $event->amount()->value()
        ));
    }
}
