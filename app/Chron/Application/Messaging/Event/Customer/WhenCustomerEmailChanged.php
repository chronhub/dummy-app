<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCustomerEmailChanged
{
    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CustomerEmailChanged::class,
    )]
    public function noOp(CustomerEmailChanged $event): void
    {
    }
}
