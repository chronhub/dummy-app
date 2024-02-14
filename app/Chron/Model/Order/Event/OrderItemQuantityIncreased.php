<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderStatus;
use App\Chron\Model\Order\Quantity;
use Storm\Message\AbstractDomainEvent;

final class OrderItemQuantityIncreased extends AbstractDomainEvent
{
    public static function forOrder(OrderId $orderId, CustomerId $customerId, OrderItem $orderItem, Quantity $oldQuantity, OrderStatus $orderStatus): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'customer_id' => $customerId->toString(),
            'item' => $orderItem->toArray(),
            'old_quantity' => $oldQuantity->value,
            'order_status' => $orderStatus->value,
        ]);
    }
}
