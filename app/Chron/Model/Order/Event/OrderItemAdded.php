<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use Storm\Message\AbstractDomainEvent;

final class OrderItemAdded extends AbstractDomainEvent
{
    public static function forOrder(OrderId $orderId, CustomerId $customerId, OrderItem $orderItem): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'customer_id' => $customerId->toString(),
            'item' => $orderItem->toArray(),
        ]);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function orderItem(): OrderItem
    {
        return OrderItem::fromArray($this->content['item']);
    }
}
