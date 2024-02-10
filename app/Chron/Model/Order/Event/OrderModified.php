<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Event;

use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Amount;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderStatus;
use Storm\Message\AbstractDomainEvent;

final class OrderModified extends AbstractDomainEvent
{
    public static function forCustomer(OrderId $orderId, CustomerId $customerId, OrderStatus $orderStatus, Amount $amount): self
    {
        return new self([
            'order_id' => $orderId->toString(),
            'customer_id' => $customerId->toString(),
            'order_status' => $orderStatus->value,
            'amount' => $amount->value(),
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

    public function orderStatus(): OrderStatus
    {
        return OrderStatus::from($this->content['order_status']);
    }

    public function amount(): Amount
    {
        return Amount::fromString($this->content['amount']);
    }
}
