<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\Service\UniqueCustomerEmail;
use App\Chron\Projection\Provider\CustomerProvider;

final readonly class CustomerEmailMustBeUnique implements UniqueCustomerEmail
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function isUnique(CustomerEmail $email): bool
    {
        return $this->customerProvider->hasEmail($email->value) === false;
    }
}
