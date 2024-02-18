<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Service;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderStatus;

interface UniquePendingOrderForCustomer
{
    public function exists(CustomerId $customerId, OrderStatus $orderStatus): bool;
}
