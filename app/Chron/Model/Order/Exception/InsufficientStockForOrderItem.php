<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Exception;

use App\Chron\Model\Inventory\SkuId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItemId;

use function sprintf;

class InsufficientStockForOrderItem extends OrderException
{
    public static function withId(OrderId $orderId, SkuId $skuId, OrderItemId $orderItemId): self
    {
        return new self(
            sprintf(
                'Insufficient stock for order item "%s" with sku "%s" in order "%s".',
                $orderItemId->toString(),
                $skuId->toString(),
                $orderId->toString(),
            )
        );
    }
}
