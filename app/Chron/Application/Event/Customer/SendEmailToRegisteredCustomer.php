<?php

declare(strict_types=1);

namespace App\Chron\Application\Event\Customer;

use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRegistered::class,
    priority: 1
)]
class SendEmailToRegisteredCustomer
{
    public function __invoke(CustomerRegistered $event): void
    {
        logger('SendEmailToRegisteredCustomer');
    }
}
