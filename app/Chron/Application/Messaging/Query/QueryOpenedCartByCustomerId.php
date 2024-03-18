<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

use App\Chron\Model\Customer\CustomerId;

final readonly class QueryOpenedCartByCustomerId
{
    public function __construct(private string $customerId)
    {

    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->customerId);
    }
}
