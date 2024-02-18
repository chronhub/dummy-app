<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\Service\UniqueCustomerEmail;
use App\Chron\Projection\Provider\CustomerEmailProvider;

final readonly class CustomerEmailMustBeUnique implements UniqueCustomerEmail
{
    public function __construct(private CustomerEmailProvider $customerEmailProvider)
    {
    }

    public function isUnique(CustomerEmail $email): bool
    {
        return $this->customerEmailProvider->isUnique($email);
    }
}
