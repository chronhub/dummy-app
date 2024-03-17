<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Factory\CustomerFactory;
use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Application\Messaging\Query\QueryRandomCustomer;
use App\Chron\Package\Reporter\Report;
use App\Chron\Package\Support\QueryPromiseTrait;
use DomainException;
use stdClass;

final readonly class CustomerApplicationService
{
    use QueryPromiseTrait;

    public function registerCustomers(int $limit = 1000): void
    {
        $data = CustomerFactory::makeMany($limit);

        foreach ($data as $customerData) {
            $command = RegisterCustomer::withData(...$customerData);

            Report::relay($command);
        }
    }

    public function registerCustomer(array $data): void
    {
        $command = RegisterCustomer::withData(...$data);

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
