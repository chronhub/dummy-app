<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Domain\Event\CustomerRegistered;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRegistered::class,
    method: 'onEvent',
    priority: 3,
)]
final class SendEmailToRegisteredCustomer
{
    public function onEvent(CustomerRegistered $event): void
    {
        logger('Send email to registered customer: '.$event->content['customer_email']);
    }
}
