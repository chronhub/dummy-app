<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerAlreadyExists;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Customer\Service\UniqueEmail;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: ChangeCustomerEmail::class,
)]
final readonly class ChangeCustomerEmailHandler
{
    public function __construct(
        private CustomerCollection $customers,
        private UniqueEmail $uniqueEmail,
    ) {
    }

    public function __invoke(ChangeCustomerEmail $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        $customer = $this->customers->get($customerId);

        if (! $customer instanceof Customer) {
            throw CustomerNotFound::withId($customerId);
        }

        $customerEmail = CustomerEmail::fromString($command->content['customer_new_email']);

        if ($customer->email()->sameValueAs($customerEmail)) {
            return;
        }

        if (! $this->uniqueEmail->isUnique($customerEmail)) {
            throw CustomerAlreadyExists::withEmail($customerEmail);
        }

        $customer->changeEmail($customerEmail);

        $this->customers->save($customer);
    }
}
