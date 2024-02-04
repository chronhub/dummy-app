<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;
use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\CustomerName;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use RuntimeException;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: RegisterCustomer::class,
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(private CustomerCollection $customers)
    {
    }

    public function __invoke(RegisterCustomer $command): void
    {
        $customerId = CustomerId::fromString($command->content['id']);

        if ($this->customers->get($customerId) !== null) {
            throw new RuntimeException('Customer already exists');
        }

        $customer = Customer::register(
            $customerId,
            CustomerEmail::fromString($command->content['email']),
            CustomerName::fromString($command->content['name'])
        );

        $this->customers->save($customer);
    }
}
