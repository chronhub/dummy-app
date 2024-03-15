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

    public function registerRandomCustomers(int $limit = 1000): void
    {
        $i = 1;

        while ($i <= $limit) {
            $this->registerRandomCustomer();

            $i++;
        }
    }

    public function registerCustomer(array $data): void
    {
        $command = RegisterCustomer::withData(...$data);

        Report::relay($command);
    }

    public function registerRandomCustomer(): void
    {
        $command = RegisterCustomer::withData(
            fake()->uuid,
            $this->ensureUniqueEmail(),
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
        $name = Str::of(fake()->name)->replace(' ', '')->lower();
        $name .= Str::random(4);

        return $name.'@'.fake()->domainName;
    }

    protected function generateBirthday(): string
    {
        $year = fake()->numberBetween(1940, 2006);
        $month = fake()->numberBetween(1, 12);
        $day = fake()->numberBetween(1, 28);

        return $year.'-'.$month.'-'.$day;
    }
}
