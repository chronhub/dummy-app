<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Service;

use App\Chron\Model\Customer\CustomerEmail;

interface UniqueCustomerEmail
{
    /**
     * Check if the email is unique
     */
    public function isUnique(CustomerEmail $email): bool;
}
