<?php

declare(strict_types=1);

namespace App\Chron\Domain\Command;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\Reference;
use App\Chron\Domain\Event\CustomerRegistered;
use App\Chron\Infra\CustomerRepository;
use App\Chron\Reporter\ReportEvent;
use App\Chron\Reporter\ReportQuery;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: RegisterCustomer::class,
    //fromQueue: ['connection' => 'rabbitmq', 'name' => 'default'],
    method: 'command',
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(
        private CustomerRepository $customerRepository,
        #[Reference('reporter.event.default')] private ReportEvent $reportEvent,
        #[Reference('reporter.query.default')] private ReportQuery $reportQuery,
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
