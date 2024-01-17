<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Domain\Event\OrderMade;

#[AsMessageHandler(
    fromTransport: 'sync',
    handles: OrderMade::class,
    method: 'onEvent',
    priority: 0,
)]
final class WhenOrderMade
{
    public function onEvent(OrderMade $event): void
    {
        logger('Order made');
    }
}
