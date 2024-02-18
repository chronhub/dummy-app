<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Service;

use App\Chron\Model\Customer\CustomerId;

interface UniquePendingCustomerOrder
{
    /**
     * Check if a customer has a pending order of status 'created' or 'modified'.
     */
    public function hasPendingOrder(CustomerId $customerId): bool;
}
