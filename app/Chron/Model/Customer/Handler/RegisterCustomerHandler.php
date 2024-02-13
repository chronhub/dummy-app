<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerAddress;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use App\Chron\Model\Customer\Exception\CustomerAlreadyExists;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Customer\Service\UniqueEmail;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: RegisterCustomer::class,
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(
        private CustomerCollection $customers,
        private UniqueEmail $uniqueEmail,
    ) {
    }

    public function __invoke(RegisterCustomer $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        if ($this->customers->get($customerId) !== null) {
            throw CustomerAlreadyExists::withId($customerId);
        }

        $customerEmail = CustomerEmail::fromString($command->content['customer_email']);

        if (! $this->uniqueEmail->isUnique($customerEmail)) {
            throw CustomerAlreadyExists::withEmail($customerEmail);
        }

        $customer = Customer::register(
            $customerId,
            $customerEmail,
            CustomerName::fromString($command->content['customer_name']),
            CustomerAddress::fromArray($command->content['customer_address']),
        );

        $this->customers->save($customer);
    }
}
