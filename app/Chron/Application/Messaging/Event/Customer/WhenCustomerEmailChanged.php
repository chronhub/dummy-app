<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerEmailChanged::class,
)]
class WhenCustomerEmailChanged
{
    public function __invoke(CustomerEmailChanged $event): void
    {
        logger('CustomerEmailChanged');
    }
}
