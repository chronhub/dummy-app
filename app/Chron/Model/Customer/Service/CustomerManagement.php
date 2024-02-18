<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Repository\CustomerCollection;

final readonly class CustomerManagement
{
    public function __construct(
        private UniqueCustomerEmail $uniqueCustomerEmail,
        private CustomerCollection $customers
    ) {
    }

    public function isIdentityUnique(string $customerId): bool
    {
        return $this->customers->get(CustomerId::fromString($customerId)) === null;
    }

    public function isEmailUnique(string $email): bool
    {
        return $this->uniqueCustomerEmail->isUnique(CustomerEmail::fromString($email));
    }
}
