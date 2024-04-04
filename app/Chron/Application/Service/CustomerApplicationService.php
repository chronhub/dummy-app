<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Factory\CustomerFactory;
use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\StartCustomerRegistration;
use App\Chron\Application\Messaging\Query\QueryRandomCustomer;
use DomainException;
use stdClass;
use Storm\Support\Facade\Report;
use Storm\Support\QueryPromiseTrait;

final readonly class CustomerApplicationService
{
    use QueryPromiseTrait;

    public function registerCustomers(int $limit = 1000): void
    {
        $data = CustomerFactory::makeMany($limit);

        foreach ($data as $customerData) {
            $command = StartCustomerRegistration::withData(...$customerData);

            Report::relay($command);
        }
    }

    public function registerCustomer(array $data): void
    {
        $command = StartCustomerRegistration::withData(...$data);

        Report::relay($command);
    }

    public function changeCustomerEmail(): void
    {
        $customerId = $this->queryRandomCustomer();

        $command = ChangeCustomerEmail::withCustomer(
            $customerId,
            CustomerFactory::generateUniqueEmail()
        );

        Report::relay($command);
    }

    private function queryRandomCustomer(): string
    {
        $customer = $this->handlePromise(
            Report::relay(new QueryRandomCustomer())
        );

        if (! $customer instanceof stdClass) {
            throw new DomainException('No customer found');
        }

        return $customer->id;
    }
}
