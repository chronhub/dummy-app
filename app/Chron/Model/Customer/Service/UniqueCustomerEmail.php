<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;

final readonly class UniqueCustomerEmail
{
    public function __construct(private CustomerEmailProvider $provider)
    {
    }

    // tmp till projection
    public function isUnique(CustomerEmail $email): bool
    {
        return $this->provider->isUnique($email);
    }
}
