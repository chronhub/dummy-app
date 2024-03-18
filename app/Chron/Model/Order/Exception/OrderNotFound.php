<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItemId;
use App\Chron\Model\Order\OrderOwner;

use function sprintf;

class OrderNotFound extends DomainException
{
    public static function withId(OrderId $id): self
    {
        return new self("Order with id {$id->toString()} not found");
    }

    public static function withOrderItemId(OrderId $orderId, OrderItemId $orderItemId): self
    {
        return new self(sprintf(
            'Order item with id %s not found in order %s',
            $orderItemId->toString(),
            $orderId->toString()
        ));
    }

    public static function withOrderOwner(OrderOwner $orderOwner, OrderId $orderId): self
    {
        return new self(sprintf(
            'Order owner with id %s not found for order id %s',
            $orderOwner->toString(),
            $orderId->toString(),
        ));
    }
}
