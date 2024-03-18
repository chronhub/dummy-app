<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Order\Balance;
use App\Chron\Model\Order\ItemCollection;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\OrderStatus;
use App\Chron\Model\Order\Quantity;
use Storm\Message\AbstractDomainEvent;

final class OrderPaid extends AbstractDomainEvent
{
    public static function forOrder(OrderId $orderId, OrderOwner $orderOwner, ItemCollection $items, OrderStatus $orderStatus): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'order_owner' => $orderOwner->toString(),
            'order_status' => $orderStatus->value,
            'order_balance' => $items->calculateBalance()->value(),
            'order_quantity' => $items->calculateQuantity()->value,
            'items' => $items->toArray(),
        ]);
    }

    public function aggregateId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->content['order_owner']);
    }

    public function orderStatus(): OrderStatus
    {
        return OrderStatus::from($this->content['order_status']);
    }

    public function orderBalance(): Balance
    {
        return Balance::fromString($this->content['order_balance']);
    }

    public function orderQuantity(): Quantity
    {
        return Quantity::create($this->content['order_quantity']);
    }

    public function orderItems(): ItemCollection
    {
        return ItemCollection::fromArray($this->aggregateId(), $this->content['items']);
    }
}
