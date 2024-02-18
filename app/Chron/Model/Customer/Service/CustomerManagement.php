<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Repository\CustomerCollection;

final readonly class CustomerManagement
{
    public function __construct(private CustomerCollection $customers)
    {
    }

    public function exists(string $customerId): bool
    {
        return $this->customers->get(CustomerId::fromString($customerId)) instanceof Customer;
    }
}
