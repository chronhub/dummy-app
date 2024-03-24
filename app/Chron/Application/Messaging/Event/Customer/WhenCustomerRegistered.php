<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Application\Service\AuthApplicationService;
use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCustomerRegistered
{
    public function __construct(
        private CartApplicationService $cartApplicationService,
        private AuthApplicationService $authApplicationService
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CustomerRegistered::class,
        priority: 1
    )]
    public function sendEmailToNewCustomer(CustomerRegistered $event): void
    {
        // send email to new customer
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CustomerRegistered::class,
        priority: 2
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
        reporter: 'reporter.event.sync.default',
        handles: CustomerRegistered::class,
        priority: 4
    )]
    public function createCartForCustomer(CustomerRegistered $event): void
    {
        $this->cartApplicationService->openCart($event->aggregateId()->toString());
    }
}
