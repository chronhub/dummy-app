<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Application\Service\OrderService;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\CustomerEmailReadModel;
use App\Chron\Projection\ReadModel\CustomerReadModel;

final readonly class WhenCustomerRegistered
{
    public function __construct(
        private CustomerReadModel $customerReadModel,
        private CustomerEmailReadModel $customerEmailReadModel,
        private OrderService $orderService,
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 0
    )]
    public function storeNewCustomer(CustomerRegistered $event): void
    {
        $this->customerReadModel->insert(
            $event->aggregateId()->toString(),
            $event->email()->value,
            $event->name()->value,
            $event->address()->street,
            $event->address()->city,
            $event->address()->postalCode,
            $event->address()->country
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 1
    )]
    public function storeCustomerEmail(CustomerRegistered $event): void
    {
        $this->customerEmailReadModel->insert(
            $event->aggregateId()->toString(),
            $event->email()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 2
    )]
    public function sendEmailToNewCustomer(CustomerRegistered $event): void
    {
        // send email to new customer
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 3
    )]
    /**
     * simplify order creation for new customer till no application cart.
     */
    public function createOrderForCustomer(CustomerRegistered $event): void
    {
        $this->orderService->createOrder($event->aggregateId()->toString());
    }
}
