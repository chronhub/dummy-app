<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Model\Customer\Event\CustomerEmailChanged;
use App\Chron\Projection\ReadModel\CustomerEmailReadModel;
use App\Chron\Projection\ReadModel\CustomerReadModel;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCustomerEmailChanged
{
    public function __construct(
        private CustomerReadModel $customerReadModel,
        private CustomerEmailReadModel $customerEmailReadModel
    ) {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerEmailChanged::class,
        priority: 0
    )]
    public function updateCustomerEmail(CustomerEmailChanged $event): void
    {
        $this->customerReadModel->updateEmail(
            $event->aggregateId()->toString(),
            $event->newEmail()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: CustomerEmailChanged::class,
        priority: 1
    )]
    public function storeNewCustomerEmail(CustomerEmailChanged $event): void
    {
        $this->customerEmailReadModel->insert(
            $event->aggregateId()->toString(),
            $event->newEmail()->value
        );
    }
}
