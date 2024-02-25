<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Customer\ChangeCustomerEmail;
use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Model\Customer\Gender;
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

    public function registerCustomers(int $limit = 1000): void
    {
        $i = 1;

        while ($i <= $limit) {
            $this->registerCustomer();

            $i++;
        }
    }

    public function registerCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            fake()->email,
            fake()->name,
            fake()->randomElement(Gender::toStrings()),
            $this->generateBirthday(),
            fake()->phoneNumber,
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
        return Str::random(16).'@'.fake()->domainName;
    }

    protected function generateBirthday(): string
    {
        $year = fake()->numberBetween(1940, 2006);
        $month = fake()->numberBetween(1, 12);
        $day = fake()->numberBetween(1, 28);

        return $year.'-'.$month.'-'.$day;
    }
}
