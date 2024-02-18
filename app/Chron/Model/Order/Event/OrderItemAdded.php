<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderItemId;
use App\Chron\Model\Order\OrderOwner;
use Storm\Message\AbstractDomainEvent;

final class OrderItemAdded extends AbstractDomainEvent
{
    public static function forOrder(OrderId $orderId, OrderOwner $orderOwner, OrderItem $orderItem): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'order_owner' => $orderOwner->toString(),
            'order_item' => $orderItem->toArray(),
        ]);
    }

    public function aggregateId(): OrderItemId
    {
        return OrderItemId::fromString($this->content['order_item']['order_item_id']);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->content['order_owner']);
    }

    public function orderItem(): OrderItem
    {
        return OrderItem::fromArray($this->content['order_item']);
    }
}
