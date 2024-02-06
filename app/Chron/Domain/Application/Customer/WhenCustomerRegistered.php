<?php

declare(strict_types=1);

namespace App\Chron\Domain\Application\Customer;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Attribute\Reference\Reference;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Model\Order\Command\CreateOrder;
use App\Chron\Reporter\ReportCommand;

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
