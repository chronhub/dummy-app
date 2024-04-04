<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\Exception\CustomerAlreadyExists;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Customer\Service\UniqueCustomerEmail;
use Storm\Message\Attribute\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.sync.default',
    handles: RegisterCustomer::class,
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(
        private CustomerCollection $customers,
        private UniqueCustomerEmail $uniqueEmail,
    ) {
    }

    public function __invoke(RegisterCustomer $command): void
    {
        $customerId = $command->customerId();

        if ($this->customers->get($customerId) !== null) {
            throw CustomerAlreadyExists::withId($customerId);
        }

        $customerEmail = $command->customerEmail();

        if (! $this->uniqueEmail->isUnique($customerEmail)) {
            throw CustomerAlreadyExists::withEmail($customerEmail);
        }

        $customer = Customer::register(
            $customerId,
            $customerEmail,
            $command->customerName(),
            $command->customerGender(),
            $command->customerBirthday(),
            $command->customerPhoneNumber(),
            $command->customerAddress(),
        );

        $this->customers->save($customer);
    }
}
