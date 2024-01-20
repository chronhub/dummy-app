<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\MessageHandler\AsMessageHandler;
use App\Chron\Domain\Event\CustomerRegistered;

use function sprintf;

final class WhenCustomerRegistered
{
    #[AsMessageHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        method: 'onEvent',
        priority: 1,
    )]
    public function onEvent(CustomerRegistered $event): void
    {
        logger(sprintf('Customer registered with email: %s', $event->content['customer_email']));
    }

    #[AsMessageHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 2,
    )]
    public function mySecondHandler(CustomerRegistered $event): void
    {
        logger(sprintf('React on second handler with customer registered with email: %s', $event->content['customer_email']));
    }
}
