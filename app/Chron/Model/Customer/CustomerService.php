<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Application\Messaging\Query\QueryRandomCustomer;
use App\Chron\Package\Reporter\Report;
use Illuminate\Support\Str;
use RuntimeException;
use Storm\Support\QueryPromiseTrait;

class CustomerService
{
    use QueryPromiseTrait;

    public function registerCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            $this->ensureUniqueEmail(),
            fake()->name
        );

        Report::relay($command);
    }

    public function changeCustomerEmail(): void
    {
        $customerId = $this->findRandomCustomer();

        $command = ChangeCustomerEmail::withCustomer($customerId, $this->ensureUniqueEmail());

        Report::relay($command);
    }

    protected function findRandomCustomer(): string
    {
        $customer = $this->handlePromise(Report::relay(new QueryRandomCustomer()));

        if ($customer === null) {
            throw new RuntimeException('No customer found');
        }

        return $customer->id;
    }

    protected function ensureUniqueEmail(): string
    {
        return Str::random(32).'@'.fake()->domainName;
    }
}
