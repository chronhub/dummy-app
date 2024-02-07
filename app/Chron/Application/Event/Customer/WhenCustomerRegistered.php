<?php

declare(strict_types=1);

namespace App\Chron\Application\Event\Customer;

use App\Chron\Application\Command\Order\CreateOrder;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Package\Attribute\Reference\Reference;
use App\Chron\Package\Reporter\ReportCommand;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRegistered::class,
    priority: 2
)]
final readonly class WhenCustomerRegistered
{
    public function __construct(#[Reference('reporter.command.default')] private ReportCommand $reporter)
    {
    }

    public function __invoke(CustomerRegistered $event): void
    {
        $this->reporter->relay(CreateOrder::forCustomer(
            $event->customerId()->toString(),
            fake()->uuid(),
        ));
    }
}
