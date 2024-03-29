<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Application\Service\AuthApplicationService;
use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Projection\ReadModel\CustomerEmailReadModel;
use App\Chron\Projection\ReadModel\CustomerReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCustomerRegistered
{
    public function __construct(
        private CustomerReadModel $customerReadModel,
        private CustomerEmailReadModel $customerEmailReadModel,
        private CartApplicationService $cartApplicationService,
        private AuthApplicationService $authApplicationService
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
            $event->gender()->value,
            $event->birthday()->value,
            $event->phoneNumber()->value,
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
    public function createAuthUser(CustomerRegistered $event): void
    {
        $this->authApplicationService->createAuthUser(
            $event->aggregateId()->toString(),
            $event->name()->value,
            $event->email()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerRegistered::class,
        priority: 4
    )]
    public function createCartForCustomer(CustomerRegistered $event): void
    {
        $this->cartApplicationService->openCart($event->aggregateId()->toString());
    }
}
