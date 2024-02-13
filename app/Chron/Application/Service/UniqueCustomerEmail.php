<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Model\Customer\Service\UniqueEmail;
use App\Chron\Projection\Provider\CustomerEmailProvider;

final readonly class UniqueCustomerEmail implements UniqueEmail
{
    public function __construct(private CustomerEmailProvider $provider)
    {
    }

    public function isUnique(CustomerEmail $email): bool
    {
        return $this->provider->isUnique($email);
    }
}
