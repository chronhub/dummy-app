<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CustomerProvider;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;

final readonly class CustomerService
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function registerCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            $this->ensureUniqueEmail(),
            fake()->name,
            [
                'street' => fake()->streetAddress,
                'city' => fake()->city,
                'postal_code' => fake()->postcode,
                'country' => fake()->country,
            ]
        );

        Report::relay($command);
    }

    public function changeCustomerEmail(): void
    {
        $customer = $this->findRandomCustomer();

        $command = ChangeCustomerEmail::withCustomer($customer->id, $this->ensureUniqueEmail());

        Report::relay($command);
    }

    public function findRandomCustomer(): stdClass
    {
        $customer = $this->customerProvider->findRandomCustomer();

        if ($customer === null) {
            throw new RuntimeException('No customer found');
        }

        return $customer;
    }

    protected function ensureUniqueEmail(): string
    {
        return Str::random(32).'@'.fake()->domainName;
    }
}
