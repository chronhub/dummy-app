<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Attribute\Reference;
use App\Chron\Domain\Event\CustomerEmailUpdated;
use App\Chron\Infra\CustomerRepository;
use Storm\Reporter\ReportEvent;

#[AsMessageHandler(
    reporter: 'reporter.command.default',
    fromTransport: 'async',
    handles: UpdateCustomerEmail::class,
    method: 'command',
    priority: 0,
)]
final readonly class UpdateCustomerEmailHandler
{
    public function __construct(
        private CustomerRepository $customerRepository,
        #[Reference('reporter.event.default')] private ReportEvent $reportEvent,
    ) {
    }

    public function command(UpdateCustomerEmail $command): void
    {
        $this->customerRepository->updateRandomCustomerEmail($command->headers());

        $event = CustomerEmailUpdated::withCustomer();

        $this->reportEvent->relay($event);
    }
}
