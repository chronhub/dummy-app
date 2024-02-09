<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Package\Reporter\Report;
use Illuminate\Database\Connection;
use Illuminate\Support\Str;

class CustomerService
{
    public function registerCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            Str::random(32).'@'.fake()->domainName,
            fake()->name
        );

        Report::relay($command);
    }

    public function changeCustomerEmail(): void
    {
        $customerId = $this->findRandomCustomer();

        $command = ChangeCustomerEmail::withCustomer($customerId, fake()->email);

        Report::relay($command);
    }

    protected function findRandomCustomer(): string
    {
        /** @var Connection $connection */
        $connection = app('db.connection');

        $customer = $connection->table('customer')->inRandomOrder()->first();

        return $customer->id;
    }
}
