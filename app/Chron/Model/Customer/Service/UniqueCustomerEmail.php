<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;

interface UniqueCustomerEmail
{
    public function isUnique(CustomerEmail $email): bool;
}
