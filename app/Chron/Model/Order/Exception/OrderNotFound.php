<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Order\OrderId;

class OrderNotFound extends DomainException
{
    public static function withId(OrderId $id): self
    {
        return new self("Order with id {$id->toString()} not found");
    }
}
