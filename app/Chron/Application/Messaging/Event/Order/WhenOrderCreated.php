<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderCreated;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenOrderCreated
{
    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderCreated::class,
    )]
    public function noOp(OrderCreated $event): void
    {
    }
}
