<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;

final readonly class QueryOrderOfCustomer
{
    public function __construct(
        private string $customerId,
        private string $orderId
    ) {
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->customerId);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->orderId);
    }
}
