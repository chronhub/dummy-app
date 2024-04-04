<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Handler;

use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Application\Messaging\Command\Customer\StartCustomerRegistration;
use Storm\Message\Attribute\AsCommandHandler;
use Storm\Support\Facade\Report;

#[AsCommandHandler(
    reporter: 'reporter.command.async.default',
    handles: StartCustomerRegistration::class,
)]
final class StartCustomerRegistrationHandler
{
    public function __invoke(StartCustomerRegistration $command): void
    {
        Report::relay(RegisterCustomer::withData(
            $command->customerId()->toString(),
            $command->customerEmail()->value,
            $command->customerName()->value,
            $command->customerGender()->value,
            $command->customerBirthday()->value,
            $command->customerPhoneNumber()->value,
            $command->customerAddress()->toArray()
        ));
    }
}
