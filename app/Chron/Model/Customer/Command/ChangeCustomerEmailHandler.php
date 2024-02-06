<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerAlreadyExists;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Customer\Service\CustomerEmailProvider;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: ChangeCustomerEmail::class,
)]
final readonly class ChangeCustomerEmailHandler
{
    public function __construct(
        private CustomerCollection $customers,
        private CustomerEmailProvider $customerEmailProvider,
    ) {
    }

    public function __invoke(ChangeCustomerEmail $command): void
    {
        $customerId = CustomerId::fromString($command->content['id']);
        $customerEmail = CustomerEmail::fromString($command->content['email']);

        $customer = $this->customers->get($customerId);

        if (! $customer instanceof Customer) {
            throw CustomerNotFound::withId($customerId);
        }

        if ($customer->email()->equalsTo($customerEmail)) {
            return;
        }

        if (! $this->customerEmailProvider->isUnique($customerEmail)) {
            throw CustomerAlreadyExists::withEmail($customerEmail);
        }

        $customer->changeEmail($customerEmail);

        $this->customers->save($customer);

        $this->customerEmailProvider->insert($customerId, $customerEmail);
    }
}
