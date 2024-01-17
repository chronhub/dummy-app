<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Domain\Event\CustomerEmailUpdated;

#[AsMessageHandler(
    fromTransport: 'sync',
    handles: CustomerEmailUpdated::class,
    method: 'onEvent',
    priority: 0,
)]
final class WhenCustomerEmailUpdated
{
    public function onEvent(CustomerEmailUpdated $event): void
    {
        logger('Customer email updated');
    }
}
