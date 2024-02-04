<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use RuntimeException;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: ChangeCustomerEmail::class,
)]
final readonly class ChangeCustomerEmailHandler
{
    public function __construct(private CustomerCollection $customers)
    {
    }

    public function __invoke(ChangeCustomerEmail $command): void
    {
        $customerId = CustomerId::fromString($command->content['id']);

        $customer = $this->customers->get($customerId);

        if (! $customer instanceof Customer) {
            throw new RuntimeException('Customer not found');
        }

        $customer->changeEmail(CustomerEmail::fromString($command->content['new_email']));

        $this->customers->save($customer);
    }
}
