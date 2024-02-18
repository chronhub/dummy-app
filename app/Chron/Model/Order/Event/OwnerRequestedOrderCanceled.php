<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\OrderStatus;
use Storm\Message\AbstractDomainEvent;

final class OwnerRequestedOrderCanceled extends AbstractDomainEvent
{
    public static function forOrder(OrderId $orderId, OrderOwner $orderOwner, OrderStatus $orderStatus, string $reason): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'order_owner' => $orderOwner->toString(),
            'order_status' => $orderStatus->value,
            'order_canceled_reason' => $reason,
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

    public function reason(): string
    {
        return $this->content['order_canceled_reason'];
    }
}
