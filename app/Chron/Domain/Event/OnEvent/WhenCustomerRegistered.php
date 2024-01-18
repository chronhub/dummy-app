<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Domain\Event\CustomerRegistered;

final class WhenCustomerRegistered
{
    #[AsMessageHandler(
        reporter: 'reporter.event.default',
        fromTransport: 'sync',
        handles: CustomerRegistered::class,
        method: 'onEvent',
        priority: 1,
    )]
    public function onEvent(CustomerRegistered $event): void
    {
        logger('Customer registered with email: '.$event->email.' and name: '.$event->name.' and id: '.$event->customerId);
    }

    #[AsMessageHandler(
        reporter: 'reporter.event.default',
        fromTransport: 'sync',
        handles: CustomerRegistered::class,
        method: 'mySecondHandler',
        priority: 2,
    )]
    public function mySecondHandler(CustomerRegistered $event): void
    {
        logger('react on event with second handler');
    }
}
