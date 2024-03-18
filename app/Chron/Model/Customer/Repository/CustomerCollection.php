<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer\Repository;

use App\Chron\Model\Customer\Customer;
use App\Chron\Model\Customer\CustomerId;

interface CustomerCollection
{
    public function get(CustomerId $customerId): ?Customer;

    public function save(Customer $customer): void;
}
