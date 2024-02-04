<?php

declare(strict_types=1);

namespace App\Chron\Domain\Application\Customer;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Model\Customer\Event\CustomerEmailChanged;

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
