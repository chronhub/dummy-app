<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItemId;

class OrderAlreadyExists extends DomainException
{
    public static function withId(OrderId $id): self
    {
        return new self("Order with id {$id->toString()} already exists");
    }

    public static function withOrderItemId(OrderId $orderId, OrderItemId $orderItemId): self
    {
        return new self("Order with id {$orderId->toString()} already contains item with id {$orderItemId->toString()}");
    }
}
