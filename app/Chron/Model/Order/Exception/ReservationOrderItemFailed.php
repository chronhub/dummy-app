<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItemId;

use function sprintf;

class ReservationOrderItemFailed extends DomainException
{
    public static function withReason(SkuId $skuId, OrderId $orderId, OrderItemId $orderItemId, string $reason): self
    {
        return new self(sprintf(
            'Reservation for order item %s of order %s with product %s failed: %s',
            $orderItemId->toString(),
            $orderId->toString(),
            $skuId->toString(),
            $reason
        ));
    }
}
