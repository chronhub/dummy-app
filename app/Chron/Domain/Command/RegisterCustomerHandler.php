<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Attribute\Reference;
use App\Chron\Domain\Event\CustomerRegistered;
use App\Chron\Infra\CustomerRepository;
use Storm\Reporter\ReportEvent;

#[AsMessageHandler(
    fromTransport: 'async',
    handles: RegisterCustomer::class,
    method: 'command',
    priority: 0,
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(
        private CustomerRepository $customerRepository,
        #[Reference('reporter.event.default')] private ReportEvent $reportEvent,
    ) {
    }

    public function command(RegisterCustomer $command): void
    {
        $this->customerRepository->createCustomer(
            $command->content['customer_id'],
            $command->headers(),
            $command->content,
        );

        $this->reportEvent->relay(CustomerRegistered::withCustomer(
            $command->content['customer_id'],
            $command->content['customer_name'],
            $command->content['customer_email'])
        );
    }
}
