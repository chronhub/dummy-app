<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Order\Balance;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;

use function sprintf;

class InvalidOrderOperation extends DomainException
{
    public static function withInvalidStatus(OrderId $orderId, string $operation, OrderStatus $currentStatus): self
    {
        return new self(sprintf(
            'Invalid order operation for order %s: cannot %s when status is %s',
            $orderId->toString(),
            $operation,
            $currentStatus->value
        ));
    }

    public static function withInvalidBalance(OrderId $orderId, string $operation, Balance $balance): self
    {
        return new self(sprintf(
            'Invalid order operation for order %s: cannot %s when balance is %s',
            $orderId->toString(),
            $operation,
            $balance->value()
        ));
    }

    public static function returnOrderDisallowByPolicy(OrderId $orderId): self
    {
        return new self(sprintf(
            'Invalid order operation for order %s: cannot return order as policy is not met',
            $orderId->toString()
        ));
    }

    public static function completeOrderDisallowByPolicy(OrderId $orderId): self
    {
        return new self(sprintf(
            'Invalid order operation for order %s: cannot complete order as policy is not met',
            $orderId->toString()
        ));
    }
}
